<?php
/**
 * This class handles the downloading and updating of Schematic, so now you are able to update globally installed versions
 * or locally installed phar files directly from the file without having to download it seperatley.
 *
 * @author Andre Figueira <andre.figueira@me.com>
 */

namespace Library\Updater;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class SchematicUpdater
{

    /** @var string Main homepage of the project where the files and resources live */
    const HOMEPAGE = 'http://andrefigueira.github.io/Schematic/';

    /** @var string The stable binary location of the PHAR file */
    const TRUNK_SCHEMATIC_STABLE_BIN = 'https://github.com/andrefigueira/Schematic/raw/master/schematic.phar';

    /** @var string The temporary file created when downloaded the latest version of composer */
    const TMP_SCHEMATIC = 'tmp_schematic.phar';

    /** @var string The default install path for Schematic when it's installed globally, primarily used for debug */
    const INSTALL_PATH = '/usr/local/bin/schematic';

    /** @var string The real install path determined based on where the script is run from */
    private $installPath;

    /** @var object The instance of the progressbar class used from symfony */
    private $progress;

    /** @var string Checksum of the latest bin file */
    protected $latestVersionChecksum;

    /** @var bool Sets if we are in debug mode or not */
    public $debug = false;

    /**
     * We want an instance of the Symfony OutputInterface to print to screen and also we run setSchematicInstallPath
     * to determine which script run schematic this time to know what to update.
     *
     * @param OutputInterface $output
     * @throws \Exception
     */
    public function __construct(OutputInterface $output)
    {

        $this->output = $output;

        $this->setSchematicInstallPath();

    }

    /**
     * Fetches the current version based on the installPath, does an md5 to see the checksum
     *
     * @return string
     */
    private function getCurrentVersion()
    {

        if($this->debug)
        {

            $installPath = self::INSTALL_PATH;

        }
        else
        {

            $installPath = $this->installPath;

        }

        return md5_file($installPath);

    }

    /**
     * Fetches the checksum of the latest file on the remote
     *
     * @return string
     */
    private function getLatestVersion()
    {

        $content = trim(file_get_contents(self::HOMEPAGE . '/version'));

        return $content;

    }

    /**
     * Getter for retreiving the latest versions checksum
     *
     * @return string
     */
    public function getLatestVersionChecksum()
    {

        return $this->latestVersionChecksum;

    }

    /**
     * Checks if the versions match of if there is a difference in the versions to see if we should update
     *
     * @return bool
     */
    public function isCurrentVersionLatest()
    {

        $this->latestVersionChecksum = $this->getLatestVersion();

        return ($this->getCurrentVersion() == $this->latestVersionChecksum);

    }

    /**
     * Checker to see if the script has been run directly as php cli.php as we don't want to update the cli.php as its local
     *
     * @return bool
     */
    public function isUpdaterRunningFromCliPhp()
    {

       return (isset($_SERVER['argv']) && $_SERVER['argv'][0] == 'cli.php');

    }

    /**
     * Check if we have the script which was run and set it as the install path, as its what we would like to replace
     *
     * @throws \Exception
     */
    private function setSchematicInstallPath()
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

    /**
     * Sets up the download of the latest version from the remote, handles the creation of the progress bar also and the moving of
     * the file to the correct area
     */
    private function downloadLatestVersion()
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

    /**
     * Bootstrap method for downloading the latest version
     */
    public function updateSchematic()
    {

        $this->downloadLatestVersion();

    }

    /**
     * This downloads the actual file and progresses the progress bar, finalizes the installation
     *
     * @param $file
     * @param $chunks
     */
    private function download($file, $chunks)
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
        $this->output->writeln('<info>Installing Schematic...</info>');

        $this->replaceExistingInstall();


    }

    /**
     * Replaces the existing installation of Schematic based on the execute path of where the script was run from
     *
     * @throws \Exception
     */
    private function replaceExistingInstall()
    {

        if($this->debug)
        {

            $existingInstallPath = self::INSTALL_PATH;

        }
        else
        {

            $existingInstallPath = $this->installPath;

        }

        $checksum = md5_file(self::TMP_SCHEMATIC);

        if(@rename(self::TMP_SCHEMATIC, $existingInstallPath) === false)
        {

            throw new \Exception('Unable to replace old version... check permissions');

        }
        else
        {

            if(chmod($existingInstallPath, 0755))
            {

                $this->output->writeln('<info>Successfully installed Schematic version ' . $checksum . '</info>');

            }
            else
            {

                $this->output->writeln('<info>Successfully installed Schematic version ' . $checksum . ', but failed to set permissions, run chmod ' . self::INSTALL_PATH . ' manually to use it globally</info>');

            }

        }

    }

    /**
     * Callback method for CURLOPT_WRITEFUNCTION, this prints the chunk
     *
     * @param $ch
     * @param $str
     * @return int
     */
    private function chunk($ch, $str)
    {

        $this->progress->advance(strlen($str));

        $this->addToFile($str);

        return strlen($str);

    }

    /**
     * Appends the downloaded chunks to the file
     *
     * @param $chunk
     * @throws \Exception
     */
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

            if(@file_put_contents(self::TMP_SCHEMATIC, $chunk) === false)
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
    private function getChunk($file, $start, $end)
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
    private function getSize($url)
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