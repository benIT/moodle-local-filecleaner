<?php
require_once("$CFG->libdir/filestorage/file_storage.php");

/**
 * The purpose of this class is to cleanup unused files
 * @see lib/moodlelib.php:5980
 * @see lib/filestorage/file_storage.php:2245
 * Class FileCleaner
 */
class FileCleaner extends file_storage
{
    /**
     * since file_storage only has a private $trashdir member without getter, we have to create a child member to store that
     * @var string
     *
     */
    private $trashdir;


    /**
     * OrphanFileCleaner constructor.
     * @param string $delayToRemoveFromNow delay in seconds: example: 60 * 60 * 24 for one day
     */
    public function __construct()
    {
        global $CFG;
        if (isset($CFG->filedir)) {
            $filedir = $CFG->filedir;
        } else {
            $filedir = $CFG->dataroot . '/filedir';
        }
        if (isset($CFG->trashdir)) {
            $this->trashdir = $CFG->trashdir;
        } else {
            $this->trashdir = $CFG->dataroot . '/trashdir';
        }
//        echo sprintf('instanciating %s object %s', __CLASS__, PHP_EOL);
        parent::__construct($filedir, $this->trashdir, "$CFG->tempdir/filestorage", $CFG->directorypermissions, $CFG->filepermissions);
    }

    /**
     * clean up function
     * @see lib/filestorage/file_storage.php:2245 cron function
     */
    public function clean($delayToRemoveFromNow)
    {
        global $CFG, $DB;
        require_once($CFG->libdir . '/cronlib.php');

        // find out all stale draft areas (older than 4 days) and purge them
        // those are identified by time stamp of the /. root dir
        mtrace('Deleting old draft files... ', '');
        cron_trace_time_and_memory();

        //HERE IS THE TRICK: time has been replaced from $old = time() - 60 * 60 * 24 * 4; => $old = time() - $this->delayToRemoveFromNow;
        $old = time() - $delayToRemoveFromNow;
        $sql = "SELECT *
                  FROM {files}
                 WHERE component = 'user' AND filearea = 'draft' AND filepath = '/' AND filename = '.'
                       AND timecreated < :old";
        $rs = $DB->get_recordset_sql($sql, array('old' => $old));
        foreach ($rs as $dir) {
            $this->delete_area_files($dir->contextid, $dir->component, $dir->filearea, $dir->itemid);
        }
        $rs->close();
        mtrace('done.');

        // remove orphaned preview files (that is files in the core preview filearea without
        // the existing original file)
        mtrace('Deleting orphaned preview files... ', '');
        cron_trace_time_and_memory();
        $sql = "SELECT p.*
                  FROM {files} p
             LEFT JOIN {files} o ON (p.filename = o.contenthash)
                 WHERE p.contextid = ? AND p.component = 'core' AND p.filearea = 'preview' AND p.itemid = 0
                       AND o.id IS NULL";
        $syscontext = context_system::instance();
        $rs = $DB->get_recordset_sql($sql, array($syscontext->id));
        foreach ($rs as $orphan) {
            $file = $this->get_file_instance($orphan);
            if (!$file->is_directory()) {
                $file->delete();
            }
        }
        $rs->close();
        mtrace('done.');

        // remove trash pool files once a day
        // if you want to disable purging of trash put $CFG->fileslastcleanup=time(); into config.php
        if (empty($CFG->fileslastcleanup) or $CFG->fileslastcleanup < time() - 60 * 60 * 24) {
            require_once($CFG->libdir . '/filelib.php');
            // Delete files that are associated with a context that no longer exists.
            mtrace('Cleaning up files from deleted contexts... ', '');
            cron_trace_time_and_memory();
            $sql = "SELECT DISTINCT f.contextid
                    FROM {files} f
                    LEFT OUTER JOIN {context} c ON f.contextid = c.id
                    WHERE c.id IS NULL";
            $rs = $DB->get_recordset_sql($sql);
            if ($rs->valid()) {
                $fs = get_file_storage();
                foreach ($rs as $ctx) {
                    $fs->delete_area_files($ctx->contextid);
                }
            }
            $rs->close();
            mtrace('done.');
            mtrace('Deleting trash files... ', '');
            cron_trace_time_and_memory();
            fulldelete($this->trashdir);
            set_config('fileslastcleanup', time());
            mtrace('done.');
        }
    }


    /**
     * @param $time
     */
    public function resetFileslastcleanup($time)
    {
        set_config('fileslastcleanup', $time);
    }

    public function getFileslastcleanup($humanFormat = false)
    {
        global $CFG;
        if ($humanFormat) {
            return date('Y-m-d', $CFG->fileslastcleanup);
        } else {
            return $CFG->fileslastcleanup;
        }
    }


}