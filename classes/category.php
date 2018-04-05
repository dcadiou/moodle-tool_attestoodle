<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This is the class describing a category in Attestoodle
 *
 * @package    block_attestoodle
 * @copyright  2017 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle;

defined('MOODLE_INTERNAL') || die;

class category {
    /** @var string Id of the category */
    private $id;

    /** @var string Name of the category */
    private $name;

    /** @var string Description of the category */
    private $description;

    /** @var string Define if the category is a training */
    private $istraining;

    /** @var category|null Parent category of the current category */
    private $parent;

    /**
     * Constructor of the category class
     *
     * @param string $id Id of the category
     */
    public function __construct($id) {
        $this->id = $id;
        $this->name = null;
        $this->description = null;
        $this->istraining = null;
        $this->parent = null;
    }

    /**
     * Set the properties of the category
     *
     * @param string $name Name of the category
     * @param string $description Description of the category
     * @param boolean $istraining If the category is a training
     * @param category|null $parent The parent category, if any
     */
    public function feed($name, $description, $istraining, $parent) {
        $this->name = $name;
        $this->description = $description;
        $this->istraining = $istraining;
        $this->parent = $parent;
    }

    /**
     * Returns the current category informations in an array
     *
     * @todo Used to display in a moodle html_table object. It has to be
     * made in a specific UI class
     *
     * @return array The array containing the category informations
     */
    public function get_data_as_table() {
        return [
                $this->id,
                $this->name,
                $this->description
            ];
    }

    /**
     * Returns the current category informations as an stdClass object
     *
     * @todo Used to display in a moodle html_table object. It has to be
     * made in a specific UI class
     *
     * @return stdClass The stdClass containing the category informations
     */
    public function get_object_as_stdclass() {
        $obj = new \stdClass();
        $obj->id = $this->id;
        $obj->name = $this->name;
        $obj->desc = $this->description;

        return $obj;
    }

    /**
     * Update the current category data into the database.
     */
    public function persist() {
        global $DB;

        $obj = new \stdClass();
        $obj->id = $this->id;
        $obj->description = $this->description;

        $DB->update_record("course_categories", $obj);
    }

    /**
     * Getter for $id property
     *
     * @return string Id of the category
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Getter for $name property
     *
     * @return string Name of the category
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Getter for $istraining property
     * @return boolean Value of the istraining property
     */
    public function is_training() {
        return $this->istraining;
    }

    /**
     * Getter for $description property
     *
     * @return string Description of the category
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Getter for $parent property
     *
     * @return category|null Parent category of the current category, if any
     */
    public function get_parent() {
        return $this->parent;
    }

    /**
     * Method that check if the category has a parent
     *
     * @return boolean True if the category has a parent
     */
    public function has_parent() {
        return isset($this->parent);
    }

    /**
     * Returns the parent hierarchy of the category
     *
     * @return string the hierarchy formatted "[parent N-x] / [parent N-1] / [current category]"
     */
    public function get_hierarchy() {
        $hierarchy = "";
        if ($this->has_parent()) {
            $hierarchy = $this->get_parent()->get_hierarchy() . " / ";
        }
        return $hierarchy . $this->get_name();
    }

    /**
     * Setter for $id property
     *
     * @param string $prop Id to set for the category
     */
    public function set_id($prop) {
        $this->id = $prop;
    }

    /**
     * Setter for $name property
     *
     * @param string $prop Name to set for the category
     */
    public function set_name($prop) {
        $this->name = $prop;
    }

    /**
     * Set the $istraining property if the value is different from the current one
     *
     * @param boolean $prop Either if the category is a training or not
     * @return boolean True if the new value is different from the current one
     */
    public function set_istraining($prop) {
        if ($this->istraining != $prop) {
            $this->istraining = $prop;
            $this->update_istraining_in_description();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Setter for $description property
     *
     * @param string $prop Description to set for the category
     */
    public function set_description($prop) {
        $this->description = $prop;
    }

    private function update_istraining_in_description() {
        $desc = $this->description;
        $istraining = $this->istraining;

        $regexp = "/<span class=(?:(?:\"attestoodle_training\")|(?:\'attestoodle_training\'))><\/span>/iU";

        if (!$istraining) {
            $desc = preg_replace($regexp, "", $desc);
        } else {
            if (preg_match($regexp, $desc)) {
                $desc = preg_replace($regexp, "<span class=\"attestoodle_training\"></span>", $desc);
            } else {
                $desc = $desc . "<span class=\"attestoodle_training\"></span>";
            }
        }

        $this->set_description($desc);
    }

    /**
     * Setter for $parent property
     *
     * @param category $prop Parent category to set for the current category
     */
    public function set_parent($prop) {
        $this->parent = $prop;
    }
}
