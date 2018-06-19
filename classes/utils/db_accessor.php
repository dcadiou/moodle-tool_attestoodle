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
 * This is the singleton class that allows other classes to access the
 * database and manipulate its data.
 *
 * @package    block_attestoodle
 * @copyright  2018 Pole de Ressource Numerique de l'Université du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_attestoodle\utils;

defined('MOODLE_INTERNAL') || die;

class db_accessor extends singleton {
    /** @var db_accessor Instance of the db_accessor singleton */
    protected static $instance;

    /** @var $DB Instance of the $DB Moodle variable */
    private static $db;

    /**
     * Protected constructor to avoid external instanciation.
     *
     * @global type $DB The global moodle $DB object
     */
    protected function __construct() {
        global $DB;
        parent::__construct();
        self::$db = $DB;
    }

    /**
     * Retrieves all the course_categories in moodle DB.
     *
     * @return \stdClass Standard Moodle DB object
     */
    public function get_all_categories() {
        $result = self::$db->get_records('course_categories', null, null, 'id, name, description, parent');
        return $result;
    }

    /**
     * Retrieves all the attestoodle milestones in moodle DB.
     *
     * @return \stdClass Standard Moodle DB object
     */
    public function get_all_milestones() {
        $result = self::$db->get_records('block_attestoodle_milestone');
        return $result;
    }

    /**
     * Method that deletes an activity in the attestoodle_milestone table.
     *
     * @param activity $activity The activity to delete in table
     */
    public function delete_milestone($activity) {
        self::$db->delete_records(
                'block_attestoodle_milestone',
                array('moduleid' => $activity->get_id())
        );
    }

    /**
     * Method that insert an activity in the attestoodle_milestone table.
     *
     * @param activity $activity The activity to insert in table
     */
    public function insert_milestone($activity) {
        $dataobject = new \stdClass();
        $dataobject->milestone = $activity->get_milestone();
        $dataobject->moduleid = $activity->get_id();

        self::$db->insert_record('block_attestoodle_milestone', $dataobject);
    }

    /**
     * Method that update an activity in the attestoodle_milestone table.
     *
     * @param activity $activity The activity to update in table
     */
    public function update_milestone($activity) {
        $request = "
                UPDATE mdl_block_attestoodle_milestone
                SET milestone = ?
                WHERE moduleid = ?
            ";
        self::$db->execute(
                $request,
                array(
                        $activity->get_milestone(),
                        $activity->get_id()
                ));
    }

    /**
     * Retrieves all the attestoodle trainings in moodle DB.
     *
     * @return \stdClass Standard Moodle DB object
     */
    public function get_all_trainings() {
        $result = self::$db->get_records('block_attestoodle_training');
        return $result;
    }

    /**
     * Retrieves the path of the course categories that linked
     * to a training in Attestoodle.
     *
     * @param int[] $categoryids The ids of the categories to retrieve
     * @return \stdClass Standard Moodle DB object
     */
    public function get_categories_paths($categoryids) {
        $result = self::$db->get_records_list(
                'course_categories',
                'id',
                $categoryids,
                null,
                'path');
        return $result;
    }

    /**
     * Retrieves informations of the course categories that linked
     * to a training in Attestoodle.
     *
     * @param int[] $categoryids The ids of the categories to retrieve
     * @return \stdClass Standard Moodle DB object
     */
    public function get_categories_by_id($categoryids) {
        $result = self::$db->get_records_list(
                'course_categories',
                'id',
                $categoryids,
                null,
                'id, name, description, parent');
        return $result;
    }

    /**
     * Retrieves the courses under a specific course category (training).
     *
     * @param int $id Id of the course category to retrieve courses for
     * @return \stdClass Standard Moodle DB object
     */
    public function get_courses_by_training($id) {
        $result = self::$db->get_records('course', array('category' => $id));
        return $result;
    }

    /**
     * Retrieves the modules (activities) under a specific course.
     *
     * @param int $id Id of the course to retrieve activities for
     * @return \stdClass Standard Moodle DB object
     */
    public function get_course_modules_by_course($id) {
        $result = self::$db->get_records('course_modules', array('course' => $id));
        return $result;
    }

    /**
     * Retrieves the learners (student users) registered to a specific course
     *
     * @param int $courseid Id of the course to retrieve learners for
     * @return \stdClass Standard Moodle DB object
     */
    public function get_learners_by_course($courseid) {
        $studentroleid = get_config('attestoodle', 'student_role_id');
        $request = "
                SELECT u.id, u.firstname, u.lastname
                FROM mdl_user u
                JOIN mdl_role_assignments ra
                    ON u.id = ra.userid
                JOIN mdl_context cx
                    ON ra.contextid = cx.id
                JOIN mdl_course c
                    ON cx.instanceid = c.id
                    AND cx.contextlevel = 50
                WHERE 1=1
                    AND c.id = ?
                    AND ra.roleid = ?
                ORDER BY u.lastname
            ";
        $result = self::$db->get_records_sql($request, array($courseid, $studentroleid));

        return $result;
    }

    /**
     * Retrieves the activities IDs validated by a specific learner.
     *
     * @param learner $learner The learner to search activities for
     * @return \stdClass Standard Moodle DB object
     */
    public function get_activities_validated_by_learner($learner) {
        $result = self::$db->get_records(
                'course_modules_completion',
                array(
                    'userid' => $learner->get_id(),
                    'completionstate' => 1
                ));
        return $result;
    }

    /**
     * Retrieves the name of a module (activity type) based on its ID.
     *
     * @param int $id The module ID to search the name for
     * @return \stdClass Standard Moodle DB object
     */
    public function get_module_table_name($id) {
        $result = self::$db->get_record('modules', array('id' => $id), "name");
        return $result->name;
    }

    /**
     * Retrieves the details of an activity (module) in its specific DB table.
     *
     * @param int $instanceid Activity of the module in its specific DB table
     * @param string $tablename DB table of the module searched
     * @return \stdClass Standard Moodle DB object
     */
    public function get_course_modules_infos($instanceid, $tablename) {
        $result = self::$db->get_record($tablename, array('id' => $instanceid));
        return $result;
    }

    /**
     * Delete a training in training table based on the category ID.
     *
     * @param int $categoryid The category ID that we want to delete
     */
    public function delete_training($categoryid) {
        self::$db->delete_records('block_attestoodle_training', array('categoryid' => $categoryid));
    }

    /**
     * Insert a training in training table for a specific category ID.
     *
     * @param int $categoryid The category ID that we want to insert
     */
    public function insert_training($categoryid) {
        $dataobject = new \stdClass();
        $dataobject->name = "";
        $dataobject->categoryid = $categoryid;
        self::$db->insert_record('block_attestoodle_training', $dataobject);
    }
}
