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
 * The suggest base.
 */
abstract class suggest_base {
    /**
     * Is the suggester ready to process data
     * @return bool
     */
    abstract public function is_ready(): bool;

    /**
     * Suggests a word based on the given word
     * @param string $word
     * @return string|null
     */
    abstract public function suggest_word(string $word): ?string;

    /**
     * Is the suggester available (e.g. the library is loaded)
     * @return bool
     */
    abstract public static function is_available(): bool;

    /**
     * Gets the name for the settings
     * @return string
     */
    abstract public static function get_name(): string;

    /**
     * Instanciates the suggester for the given language
     *
     * @param string $language
     * @param ?string $spelling if defined, the local spelling (eg UK vs US)
     * @return self
     */
    abstract public static function for_language(string $language, ?string $spelling = null): self;

    /**
     * Suggests words based on the given word
     * @param string $word
     * @return array
     */
    public function suggest_words(string $word): array {
        $suggestion = static::suggest_word($word);
        return empty($suggestion) ? [] : [$suggestion];
    }

    /**
     * Suggests a sentence based on the given sentence. Base implementation does a word for word suggestion
     * @param string $sentence the original sentence to parse
     * @return string the suggested sentence (may be the same as original)
     */
    public function suggest_sentence(string $sentence): string {
        $suggested_words = [];
        $words = preg_split("/\s+/u", $sentence);
        foreach ($words as $word) {
            $suggest = $this->suggest_word($word);
            $suggested_words[] = $suggest ?? $word;
        }
        if (!empty(array_diff($words, $suggested_words))) {
            return implode(' ', $suggested_words);
        }
        // Keep original formatting
        return $sentence;
    }

    /**
     * Is the given word misspelled?
     * @param string $word to word to check
     * @return bool
     */
    public function is_misspelled(string $word): bool {
        return false;
    }

}
