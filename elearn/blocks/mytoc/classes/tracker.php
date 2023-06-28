<?php
/**
 * Output tracker.
 * 
 * @package    block_mytoc
 * @copyright  2023 CLICK-AP (https://www.click-ap.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/weblib.php');

class block_mytoc_tracker {

    /**
     * Constant to output nothing.
     */
    const NO_OUTPUT = 0;

    /**
     * Constant to output HTML.
     */
    const OUTPUT_HTML = 1;

    /**
     * Constant to output plain text.
     */
    const OUTPUT_PLAIN = 2;

    /**
     * @var array columns to display.
     */
    protected $columns = array('line', 'result', 'idno', 'firstname', 'enrol');

    /**
     * @var int row number.
     */
    protected $rownb = 0;

    /**
     * @var int chosen output mode.
     */
    protected $outputmode;

    /**
     * @var object output buffer.
     */
    protected $buffer;

    /**
     * Constructor.
     *
     * @param int $outputmode desired output mode.
     */
    public function __construct($outputmode = self::NO_OUTPUT) {
        $this->outputmode = $outputmode;
        if ($this->outputmode == self::OUTPUT_PLAIN) {
            $this->buffer = new progress_trace_buffer(new text_progress_trace());
        }
    }

    /**
     * Finish the output.
     *
     * @return void
     */
    public function finish() {
        if ($this->outputmode == self::NO_OUTPUT) {
            return;
        }

        if ($this->outputmode == self::OUTPUT_HTML) {
            echo html_writer::end_tag('table');
        }
    }

    /**
     * Output the results.
     *
     * @param int $total total courses.
     * @param int $created count of courses created.
     * @param int $updated count of courses updated.
     * @param int $deleted count of courses deleted.
     * @param int $errors count of errors.
     * @return void
     */
    public function results($total, $errors, $skipped, $enrol, $unenrol) {
        if ($this->outputmode == self::NO_OUTPUT) {
            return;
        }

        $message = array(
            get_string('import_total', 'block_mytoc', $total),
            get_string('import_enrol', 'block_mytoc', $enrol),
            get_string('import_unenrol', 'block_mytoc', $unenrol),
            get_string('import_errors', 'block_mytoc', $errors),
            get_string('import_skipped', 'block_mytoc', $skipped)
        );

        if ($this->outputmode == self::OUTPUT_PLAIN) {
            foreach ($message as $msg) {
                $this->buffer->output($msg);
            }
        } else if ($this->outputmode == self::OUTPUT_HTML) {
            $buffer = new progress_trace_buffer(new html_list_progress_trace());
            foreach ($message as $msg) {
                $buffer->output($msg);
            }
            $buffer->finished();
        }
    }

    /**
     * Output one more line.
     *
     * @param int $line line number.
     * @param bool $outcome success or not?
     * @param array $status array of statuses.
     * @param array $data extra data to display.
     * @return void
     */
    public function output($line, $outcome, $status, $data) {
        global $OUTPUT;
        if ($this->outputmode == self::NO_OUTPUT) {
            return;
        }

        if ($this->outputmode == self::OUTPUT_PLAIN) {
            $message = array(
                $line,
                $outcome ? 'OK' : 'NOK',
                isset($data['idno']) ? $data['idno'] : '',
                isset($data['firstname']) ? $data['firstname'] : '',
                isset($data['enrol']) ? $data['enrol'] : ''
            );
            $this->buffer->output(implode("\t", $message));
            if (!empty($status)) {
                foreach ($status as $st) {
                    $this->buffer->output($st, 1);
                }
            }
        } else if ($this->outputmode == self::OUTPUT_HTML) {
            $ci = 0;
            $this->rownb++;
            if (is_array($status)) {
                $status = implode(html_writer::empty_tag('br'), $status);
            }
            if ($outcome) {
                $outcome = $OUTPUT->pix_icon('i/valid', '');
            } else {
                $outcome = $OUTPUT->pix_icon('i/invalid', '');
            }
            echo html_writer::start_tag('tr', array('class' => 'r' . $this->rownb % 2));
            echo html_writer::tag('td', $line, array('class' => 'c' . $ci++));
            echo html_writer::tag('td', $outcome, array('class' => 'c' . $ci++));
            echo html_writer::tag('td', isset($data['idno']) ? $data['idno'] : '', array('class' => 'c' . $ci++));
            echo html_writer::tag('td', isset($data['firstname']) ? $data['firstname'] : '', array('class' => 'c' . $ci++));
            echo html_writer::tag('td', isset($data['enrol']) ? $data['enrol'] : '', array('class' => 'c' . $ci++));
            echo html_writer::tag('td', $status, array('class' => 'c' . $ci++));
            echo html_writer::end_tag('tr');
        }
    }

    /**
     * Start the output.
     *
     * @return void
     */
    public function start() {
        if ($this->outputmode == self::NO_OUTPUT) {
            return;
        }

        if ($this->outputmode == self::OUTPUT_PLAIN) {
            $columns = array_flip($this->columns);
            unset($columns['status']);
            $columns = array_flip($columns);
            $this->buffer->output(implode("\t", $columns));
        } else if ($this->outputmode == self::OUTPUT_HTML) {
            $ci = 0;
            echo html_writer::start_tag('table', array('class' => 'generaltable boxaligncenter flexible-wrap',
                'summary' => get_string('importresult', 'block_mytoc')));
            echo html_writer::start_tag('tr', array('class' => 'heading r' . $this->rownb));
            echo html_writer::tag('th', get_string('csvline', 'block_mytoc'),
                array('class' => 'c' . $ci++, 'scope' => 'col'));
            echo html_writer::tag('th', get_string('importresult', 'block_mytoc'), array('class' => 'c' . $ci++, 'scope' => 'col'));
            echo html_writer::tag('th', get_string('field_idno', 'block_mytoc'), array('class' => 'c' . $ci++, 'scope' => 'col'));
            echo html_writer::tag('th', get_string('field_firstname', 'block_mytoc'), array('class' => 'c' . $ci++, 'scope' => 'col'));
            echo html_writer::tag('th', get_string('field_enrol', 'block_mytoc'), array('class' => 'c' . $ci++, 'scope' => 'col'));
            echo html_writer::tag('th', get_string('field_status', 'block_mytoc'), array('class' => 'c' . $ci++, 'scope' => 'col'));
            echo html_writer::end_tag('tr');
        }
    }

}
