<?php

namespace Library\Updater;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class SchematicUpdater
{

    const HOMEPAGE = 'http://andrefigueira.github.io/Schematic/';

    const TRUNK_SCHEMATIC_STABLE_BIN = 'https://github.com/andrefigueira/Schematic/raw/master/schematic.phar';

    const TMP_SCHEMATIC = 'tmp_schematic.phar';

    public $installPath;

    public $progress;

    public function __construct(OutputInterface $output)
    {

        $this->output = $output;

        $this->setSchematicInstallPath();

    }

    public function getCurrentVersion()
    {

        return APP_VERSION;

    }

    public function getLatestVersion()
    {

        $content = trim(file_get_contents(self::HOMEPAGE . '/version'));

        return $content;

    }

    public function isCurrentVersionLatest()
    {

        return ($this->getCurrentVersion() == $this->getLatestVersion());

    }

    public function isUpdaterRunningFromCliPhp()
    {

       return (isset($_SERVER['argv']) && $_SERVER['argv'][0] == 'cli.php');

    }

    public function setSchematicInstallPath()
    {

        switch(true)
        {

            case isset($_SERVER['PHP_SELF']):
                $this->installPath = $_SERVER['PHP_SELF'];
                break;

            case isset($_SERVER['SCRIPT_NAME']):
                $this->installPath = $_SERVER['SCRIPT_NAME'];
                break;

            case isset($_SERVER['SCRIPT_FILENAME']):
                $this->installPath = $_SERVER['SCRIPT_FILENAME'];
                break;

            case isset($_SERVER['PATH_TRANSLATED']):
                $this->installPath = $_SERVER['PATH_TRANSLATED'];
                break;

            default:
                throw new \Exception('Unable to determine Schematic install path, please install manually...');

        }

    }

    public function downloadLatestVersion()
    {

        $fileSize = $this->getSize(self::TRUNK_SCHEMATIC_STABLE_BIN);

        $this->progress = new ProgressBar($this->output, $fileSize);

        $this->output->writeln('<info>Downloading Schematic</info>');

        $this->progress->start();
        $this->progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s% %filename%');
        $this->progress->setRedrawFrequency(1);
        $this->progress->setMessage(self::TRUNK_SCHEMATIC_STABLE_BIN, 'filename');


        $packetSize = 262144;

        if($packetSize > $fileSize)
        {

            $packetSize = $fileSize;

        }

        $this->download(self::TRUNK_SCHEMATIC_STABLE_BIN, $packetSize);

    }

    public function updateSchematic()
    {

        $this->downloadLatestVersion();

    }

    /**
     * This downloads the actual file
     *
     * @param $file
     * @param $chunks
     */
    public function download($file, $chunks)
    {

        set_time_limit(0);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-disposition: attachment; filename=' . basename($file));
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        header('Pragma: public');

        $size = $this->getSize($file);

        header('Content-Length: ' . $size);

        $i = 0;

        while($i <= $size)
        {

            $this->getChunk($file, (($i == 0) ? $i : $i + 1), ((($i + $chunks) > $size) ? $size : $i + $chunks));

            $i = ($i + $chunks);

        }

        $this->progress->finish();

        $this->output->writeln(PHP_EOL . '<info>Finished downloading</info>');
        $this->output->writeln(PHP_EOL . '<info>Installing Schematic...</info>');

        $this->replaceExistingInstall();


    }

    private function replaceExistingInstall()
    {

        if(@rename(self::TMP_SCHEMATIC, $this->installPath) === false)
        {

            throw new \Exception('Unable to replace old version... check permissions');

        }
        else
        {

            $this->output->writeln('<info>Successfully installed Schematic</info>');

        }

    }

    /**
     * Callback method for CURLOPT_WRITEFUNCTION, this prints the chunk
     *
     * @param $ch
     * @param $str
     * @return int
     */
    function chunk($ch, $str)
    {

        $this->progress->advance(strlen($str));

        $this->addToFile($str);

        return strlen($str);

    }

    private function addToFile($chunk)
    {

        if(file_exists(self::TMP_SCHEMATIC))
        {

            if(@file_put_contents(self::TMP_SCHEMATIC, $chunk, FILE_APPEND | LOCK_EX) === false)
            {

                throw new \Exception('Failed to download file to local area, please check permissions...');

            }

        }
        else
        {

            if(@file_put_contents(self::TMP_SCHEMATIC, $chunk, FILE_APPEND | LOCK_EX) === false)
            {

                throw new \Exception('Failed to download file and create it in the local area, please check permissions...');

            }

        }

    }

    /**
     * Gets the range of bytes from the remote file
     *
     * @param $file
     * @param $start
     * @param $end
     */
    function getChunk($file, $start, $end)
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $file);
        curl_setopt($ch, CURLOPT_RANGE, $start . '-' . $end);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, array($this, 'chunk'));

        $result = curl_exec($ch);

        curl_close($ch);

    }

    /**
     * Gets the total size of the file
     *
     * @param $url
     * @return int
     */
    function getSize($url)
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);

        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        return intval($size);

    }

}