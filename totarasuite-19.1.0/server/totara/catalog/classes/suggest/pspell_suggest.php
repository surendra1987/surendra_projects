<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Maxime Claudel <maxime.claudel@totara.com>
 * @package totara_catalog
 */

namespace totara_catalog\suggest;


defined('MOODLE_INTERNAL') || die();

/**
 * Text suggestion using the PSpell extension
 */
class pspell_suggest extends suggest_base {

    /**
     * How close does the suggestion need to be from the original word searched (% proximity)
     * @var int
     */
    private const PROXIMITY_LIMIT = 60;

    /**
     * The dictionary
     * @var \PSpell\Dictionary
     */
    private $dictionary;

    public function __construct(string $lang, ?string $spelling = null) {
        $this->dictionary = \pspell_new($lang, $spelling ?? '', '', '', PSPELL_FAST);
    }

    public function is_ready(): bool {
        return static::is_available() && !empty($this->dictionary);
    }

    public function suggest_word(string $word): ?string {
        if ($this->is_misspelled($word)) {
            // PSpell orders suggestions based on score
            $word_suggestions = \pspell_suggest($this->dictionary, $word);
            foreach ($word_suggestions as $word_suggestion) {
                $percent = 0;
                // Sometimes, word suggestions are far away from the original term, so take the first one
                // that is close enough, if there is one. Ignore 100% match as it's the same word with different case
                similar_text(strtoupper($word_suggestion), strtoupper($word), $percent);
                if ($percent > static::PROXIMITY_LIMIT && $percent < 100) {
                    return $word_suggestion;
                }
            }
        }
        return null;
    }

    public function suggest_words(string $word): array {
        if ($this->is_misspelled($word)) {
            return \pspell_suggest($this->dictionary, $word);
        }
        return [];
    }

    public function is_misspelled(string $word): bool {
        // PSpell is case-sensitive (FTS isn't), which means acronyms typed lower case will be considered misspellings
        // so checking the all upper case version as well.
        return !\pspell_check($this->dictionary, $word) && !\pspell_check($this->dictionary, strtoupper($word));
    }

    public static function is_available(): bool {
        return extension_loaded('pspell');
    }

    public static function get_name(): string {
        return get_string('suggest_pspell', 'totara_catalog');
    }

    public static function for_language(string $language, ?string $spelling = null): self {
        $instance = new self($language, $spelling);

        // Maybe the language doesn't exist for that country, try without
        if (!$instance->is_ready()) {
            $instance = new self($language);
        }
        return  $instance;
    }
}