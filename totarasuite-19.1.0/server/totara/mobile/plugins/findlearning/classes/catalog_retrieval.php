<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2018 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author David Curry <david.curry@totaralearning.com>
 * @package mobile_findlearning
 */

namespace mobile_findlearning;

use totara_catalog\catalog_retrieval as core_retrieval;

use totara_catalog\datasearch\datasearch;
use totara_catalog\datasearch\full_text_search;
use totara_catalog\hook\exclude_item;
use totara_catalog\local\full_text_search_filter;

defined('MOODLE_INTERNAL') || die();

/**
 * The catalog.
 */
class catalog_retrieval extends core_retrieval {

    /**
     * Gets a page of results
     *
     * @param int $pagesize
     * @param int $maxcount
     * @param int $limitfrom
     * @param string $selectsql
     * @param string $countsql
     * @param array $params
     * @return \stdClass containing array 'objects', int 'limitfrom', int 'maxcount' and bool 'endofrecords'
     */
    public function get_page(int $pagesize, int $maxcount, int $limitfrom, string $selectsql, string $countsql, array $params): \stdClass {
        global $DB;
        $objects = [];
        $endofrecords = false;
        $querypagesize = $pagesize; // Doesn't need to be the same as page size, but shouldn't be smaller.
        $skipped = 0;

        $providerhandler = provider_handler::instance();
        $providers = $providerhandler->get_active_providers();

        foreach ($providers as $provider) {
            $provider->prime_provider_cache(); // Fetch appropriate visibility records in bulk.
        }

        while (!$endofrecords && count($objects) < $pagesize) {
            // Get some records.
            $records = $DB->get_records_sql($selectsql, $params, $limitfrom, $querypagesize);

            // Stop if there are no more records to be retrieved from the db.
            if (empty($records)) {
                $endofrecords = true;
                break;
            }

            foreach ($records as $record) {
                $limitfrom++; // Whether or not we return this record, we don't want to process it again.

                // Skip records for providers that aren't enabled (or maybe aren't even real!).
                if (!$providerhandler->is_active($record->objecttype)) {
                    $skipped++;
                    continue;
                }

                $provider = $providerhandler->get_provider($record->objecttype);

                // Check if the object can be included in the catalog for the given user.
                $cansees = $provider->can_see([$record]);
                if (!$cansees[$record->objectid]) {
                    $skipped++;
                    continue;
                }

                // A hook here to exclude/include the course/program/certificate based on the
                // third parties setting.
                $hook = new exclude_item($record);
                $hook->execute();

                if ($hook->is_excluded()) {
                    $skipped++;
                    continue;
                }

                // Not excluded, so add it to the results;
                $objects[] = $record;

                // Stop if we've got enough objects to fill the page.
                if (count($objects) == $pagesize) {
                    break 2;
                }
            }

            $querypagesize *= 2; // Exponential growth, so that we will do about O(log n) steps at most.
        }

        // Figure out if there are any more records to load, if we didn't reach the end while calculating the results.
        if ($endofrecords) {
            $totaluncheckedrecords = $limitfrom;
        } else {
            $totaluncheckedrecords = $DB->count_records_sql($countsql, $params);
            $endofrecords = $limitfrom == $totaluncheckedrecords;
        }

        // Figure out the maximum possible number of records that MIGHT be visible, according to the calculations we've done so far.
        if ($maxcount < 0) {
            $maxcount = $totaluncheckedrecords;
        }
        $maxcount -= $skipped;

        $page = new \stdClass();
        $page->objects = $objects;
        $page->limitfrom = $limitfrom;
        $page->maxcount = $maxcount;
        $page->endofrecords = $endofrecords;

        return $page;
    }

    /**
     * Overriding totara_catalog\catalog_retrieval::get_sql()
     * Simplifies the sql required to get catalog results.
     *
     * @param string $orderbykey
     * @param boolean $fallback
     * @return array [$selectsql, $countsql, $params]
     */
    public function get_sql(string $orderbykey, bool $fallback = false): array {
        $outputcolumns = 'catalog.id, catalog.objecttype, catalog.objectid, catalog.contextid';

        list($orderbycolumns, $orderbysort) = $this->get_order_by_sql($orderbykey, $fallback);
        $outputcolumns .= ', ' . $orderbycolumns;

        $search = new datasearch(
            '{catalog} catalog',
            $outputcolumns,
            $orderbysort
        );

        foreach (filter_handler::instance()->get_mobile_filters() as $filter) {
            if ($filter->datafilter->is_active()) {
                if ($fallback && $filter->datafilter instanceof full_text_search) {
                    // If we're performing a fallback search, replace FTS search with a SQL like
                    $fallbackFilter = full_text_search_filter::create_fallback();
                    $fallbackFilter->datafilter->set_current_data($filter->datafilter->get_current_data());
                    $search->add_filter($fallbackFilter->datafilter);
                } else {
                    $search->add_filter($filter->datafilter);
                }
            }
        }

        return $search->get_sql();
    }

    /**
     * Overriding totara_catalog\catalog_retrieval::get_order_by_sql()
     * Simplified sort order function, using in this order of availability search, alpha, time
     *
     * @param string $orderbykey
     * @param string $fallback
     * @return array [$orderbycolumns, $orderbysort]
     */
    private function get_order_by_sql(string $orderbykey, bool $fallback = false): array {
        $searching = filter_handler::instance()->get_full_text_search_filter()->datafilter->is_active() && !$fallback;
        if (!$this->alphabetical_sorting_enabled()) {
            // We can't order alphabetically, so order by search/time.
            if ($searching) {
                return [
                    'catalogfts.score, catalog.sorttime',
                    'catalogfts.score DESC, catalog.sorttime DESC, id DESC'
                ];
            }

            return [
                'catalog.sorttime',
                'catalog.sorttime DESC, id DESC'
            ];
        } else {
            if ($searching) {
                return [
                    'catalogfts.score, catalog.sorttext',
                    'catalogfts.score DESC, catalog.sorttext ASC'
                ];
            }
            // Otherwise we default to ordering by
            return [
                'catalog.sorttext',
                'catalog.sorttext ASC'
            ];
        }
    }
}
