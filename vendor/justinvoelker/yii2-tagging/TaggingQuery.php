<?php

namespace justinvoelker\tagging;

/**
 * TaggingQuery represents a SELECT SQL statement in a way that is independent of DBMS
 *
 * Query provides a set of methods to facilitate the specification of different
 * clauses in a SELECT statement. These methods can be chained together.
 *
 * Though extended from yii\db\Query, portions of TaggingQuery change typical
 * behavior. Notable differences include:
 *  - `select` must be a string, not an array of fields
 *  - `limit` and `order` attribute are not part of the initial query but
 * instead are used after the items have been selected.
 *
 * Standard usage would include listing tags as options in a form select field
 * or with typeahead style plugins.
 *
 * For example:
 *
 * ```php
 * $query = new TaggingQuery;
 * // compose the query
 * $tags = $query
 *     ->select('tags')
 *     ->from('posts')
 *     ->getTags();
 * ```
 *
 * @author Justin Voelker <justin@justinvoelker.com>
 */
class TaggingQuery extends \yii\db\Query
{
    /**
     * @var array key=>value pairs of tags=>frequency
     */
    public $items;
    /**
     * @var string the character being used to separate tags in the selected column
     */
    public $delimiter = ',';
    /**
     * @var string the order used when limiting tags. Specified as key=>value
     * pairs where keys are 'name', 'freq', or 'rand' and values are SORT_ASC,
     * SORT_DESC, or true (true only used for 'rand'). Name sorts by tag name,
     * Freq sorts by the frequency of the tag followed by the tag name, and
     * Rand shuffles the order. Example: limitSort(['freq' => SORT_DESC,
     * 'name' => SORT_ASC]) would order tags first by descending frequency and
     * then within a given frequency by ascending name. Note that when 'name'
     * and 'freq' are both specified, order does not matter as frequency will
     * be the priority sort followed by name. If only one sort is specified, no
     * other sorting will be peformed on the items.
     * When a limit is applied, limitSort should be used to ensure the tags
     * returned are those that are expected.
     * Can be used in combination with displaySort in situations where the sort
     * applied when limiting items is not the same sort applied to the final
     * display order. For example, selecting the 10 most frequently used tags
     * but displaying them in alphabetic order.
     */
    public $limitSort;
    /**
     * @var string the order used when displaying tags. Specified as key=>value
     * pairs where keys are 'name', 'freq', or 'rand' and values are SORT_ASC,
     * SORT_DESC, or true (true only used for 'rand'). Name sorts by tag name,
     * Freq sorts by the frequency of the tag followed by the tag name, and
     * Rand shuffles the order. Example: displaySort(['freq' => SORT_DESC,
     * 'name' => SORT_ASC]) would order tags first by descending frequency and
     * then within a given frequency by ascending name. Note that when 'name'
     * and 'freq' are both specified, order does not matter as frequency will
     * be the priority sort followed by name. If only one sort is specified, no
     * other sorting will be performed on the items.
     * Can be used in combination with limitSort in situations where the sort
     * applied when limiting items is not the same sort applied to the final
     * display order. For example, selecting the 10 most frequently used tags
     * but displaying them in alphabetic order.
     */
    public $displaySort;
    /**
     * @var array values that should be excluded from the final list of tags.
     * All tags not in this array will be returned.
     */
    public $exclude;
    /**
     * @var array values that should the only values included in the final list
     * of tags. All values not in this array will be removed.
     */
    public $includeOnly;
    /**
     * @var string the Query limit will be not be used during initial selection
     * of records. It will be used to limit the final list of items. Original
     * limit will be set to null for initial selection with the original value
     * stored in this private attribute. This private attribute will be used to
     * limit the final items.
     */
    private $_limit;

    public function getTags($db = null)
    {
        // $items will only be an array if provided from earlier instance
        if (!is_array($this->items)) {
            $this->items = $this->getItems($db);
        }
        // Only reduceItems if exclude or includeOnly are set
        if (!empty($this->exclude) || !empty($this->includeOnly)) {
            $this->items = $this->reduceItems();
        }
        // Only limitItems if a limit was originally set
        if (!empty($this->_limit)) {
            $this->items = $this->sortItems($this->limitSort);
            $this->items = $this->limitItems();
        }
        // Perform final sorting for display
        $this->items = $this->sortItems($this->displaySort);
        return $this->items;
    }

    /**
     * Select data from database as specified by the criteria, merges all
     * results into single array after being split be delimiter, and finally
     * counts the frequency of all of the values resulting in an array of
     * key=>value pairs of tag=>frequency.
     * @param Connection $db the database connection used to generate the SQL
     * statement. If this parameter is not given, the `db` application
     * component will be used.
     * @return array items as key=>value (tag=>frequency)
     */
    public function getItems($db = null)
    {
        $items = [];
        $rows = $this->createCommand($db)->queryAll();
        foreach ($rows as $row) {
            foreach ($this->select as $selectField) {
                $items = array_merge($items, explode($this->delimiter, $row[$selectField]));
            }
        }
        $items = array_count_values(array_filter($items));
        return $items;
    }

    /**
     * Reduce the list of items according to exclude and includeOnly criteria
     * @return array item list reduced by exclude or includeOnly values
     */
    public function reduceItems()
    {
        $items = $this->items;
        if (!empty($this->exclude)) {
            $items = array_diff_key($items, array_fill_keys($this->exclude, ''));
        }
        if (!empty($this->includeOnly)) {
            $items = array_intersect_key($items, array_fill_keys($this->includeOnly, ''));
        }
        return $items;
    }

    /**
     * Sort the list of items according to criteria
     * @param array value to be sorted by and the order to be sorted (orderBy=>order)
     * @return array sorted item list
     */
    public function sortItems($sort)
    {
        $items = $this->items;
        if (is_array($sort)) {
            if (array_key_exists('freq', $sort) && array_key_exists('name', $sort)) {
                array_multisort(array_values($items), $sort['freq'], array_keys($items), $sort['name'], $items);
            } elseif (array_key_exists('freq', $sort)) {
                array_multisort(array_values($items), $sort['freq'], $items);
            } elseif (array_key_exists('name', $sort)) {
                array_multisort(array_keys($items), $sort['name'], $items);
            } elseif (array_key_exists('rand', $sort)) {
                $items = $this->shuffle_assoc($items);
            }
        }
        return $items;
    }

    /**
     * Limits the final results by limit specified
     * @return array limited item list
     */
    public function limitItems()
    {
        return array_slice($this->items, 0, $this->_limit);
    }

    /**
     * Sets items to be used if starting from existing set of values.
     * @param array $items existing items as key=>value (tag=>frequency)
     * @return array the query object itself
     */
    public function items($items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Sets the delimiter used the separate individual tags
     * @param string $delimiter as
     * @return static the query object itself
     */
    public function delimiter($delimiter)
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    /**
     * Sets the LIMIT part of the query to null while preserving the intended limit
     * @param integer $limit the limit. Use null or negative value to disable limit.
     * @return static the query object itself
     */
    public function limit($limit)
    {
        $this->limit = null;
        $this->_limit = $limit;
        return $this;
    }

    /**
     * Sets the sorting to be used when applying the limit
     * @param array the key=>value of orderBy=>order
     * @return static the query object itself
     */
    public function limitSort($limitSort)
    {
        $this->limitSort = $limitSort;
        return $this;
    }

    /**
     * Sets the sorting to be used for final presentation
     * @param array the key=>value of orderBy=>order
     * @return static the query object itself
     */
    public function displaySort($displaySort)
    {
        $this->displaySort = $displaySort;
        return $this;
    }

    /**
     * Sets the items to be excluded from final results
     * @param array key-only array of values to exclude
     * @return static the query object itself
     */
    public function exclude($exclude)
    {
        $this->exclude = $exclude;
        return $this;
    }

    /**
     * Sets the items to be included in the final results (all others excluded)
     * @param array key-only array of values to include
     * @return static the query object itself
     */
    public function includeOnly($includeOnly)
    {
        $this->includeOnly = $includeOnly;
        return $this;
    }

    /**
     * Shuffles the items into a random order
     * @param array $items items as key=>value (tag=>frequency)
     * @return static the randomized list of items
     */
    function shuffle_assoc($items)
    {
        $random = [];
        $keys = array_keys($items);
        shuffle($keys);
        foreach ($keys as $key) {
            $random[$key] = $items[$key];
        }
        return $random;
    }
}
