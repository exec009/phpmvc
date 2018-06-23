<?php
namespace CORE;
use CORE\MVC\MVC;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
class BackupManager
{
    public static function saveBackupFile(string $file)
    {
        foreach(BACKUP['storageEngines'] as $backup)
        {
            if(!$backup['active'])
                continue;
            switch($backup['type'])
            {
                case 'googleDrive':
                    break;
                case 'dropBox':
                    $dropbox = new Dropbox(new DropboxApp($backup['clientId'], $backup['clientSecret'], $backup['accessToken']));
                    $file = $dropbox->upload(new DropboxFile($file), '/'.time().'.zip', ['autorename' => false]);
                    break;
                case 'AWS3':
                    $s3 = S3Client::factory([
                        'version'     => $backup['version'],
                        'region'      => $backup['region'],
                        'credentials' => [
                            'key'    => $backup['key'],
                            'secret' => $backup['secret']
                        ]
                    ])->putObject(array(
                        'Bucket'       => $backup['bucket'],
                        'Key'          => $backup['key'],
                        'SourceFile'   => $file,
                        'ContentType'  => 'text/plain',
                        'ACL'          => 'public-read',
                        'StorageClass' => 'REDUCED_REDUNDANCY',
                        'Metadata'     => array(
                            'Database' => 'Mysql'
                        )
                    ));
                    break;
            }
        }
    }
    public static function backupDatabase()
    {
        $path = root().BACKUP['tempPath'];
        $folder = $path.time()."/";
        try
        {
            mkdir($folder);
        }
        catch(\ErrorException $e)
        {
            mkdir($path);
            mkdir($folder);
        }
        $file = '';
        foreach(DB as $key => $data)
        {
            if(($data['backup'] ?? true) == false)
                continue;
            $file = $folder.$key.'-'.time().'.sql';
            \CORE\DB\DB::backup($key, $file);
        }
        $destination = rtrim($folder,'/').'.zip';
        self::createZip($folder, $destination, true);
        self::saveBackupFile($destination);
        unlink($destination);
    }
    private static function createZip(string $file, $destination, $overwrite = false)
    {
        if($overwrite == true)
        {
            if(file_exists($destination))
                unlink($destination);
        }
        $zip = new \ZipArchive();
        if ($zip->open($destination, \ZipArchive::CREATE)!==TRUE) {
            exit("cannot open file");
        }
        if(is_dir($file))
        {
            $files = scandir($file);
            $skip = ['.','..'];
            foreach($files as $data)
            {
                if(in_array($data, $skip))
                    continue;
                $zip->addFromString(basename($file.$data),  file_get_contents($file.$data));
            }
        }
        else
            $zip->addFromString(basename($file),  file_get_contents($file));
        $zip->close();
    }
}