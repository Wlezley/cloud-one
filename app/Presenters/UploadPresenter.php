<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\HistoryLog;

class UploadPresenter extends BasePresenter
{
    /** @var HistoryLog @inject */
    public $historyLog;

    public function __construct()
    {
        \Tracy\Debugger::$showBar = false;
    }

    // BIG FOKIN' TODO: AVOID USING 'echo' (WTF I DOIN' HERE IN THE PAST ???)
    public function actionDefault($hash): void
    {
        if ($hash == '1aerg6384areg651dfb8atr468hzz4ar6t84t541' && isset($_FILES['fileToUpload'])) {
            // $this->template->hash = $hash;

            // if (!isset($_FILES['fileToUpload'])) {
            //     echo 'E: FTU/FNF';
            //     return;
            // }

            $target_dir = 'uploads/';
            $target_file = $target_dir . basename($_FILES['fileToUpload']['name']);
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if image file is a actual image or fake image
            if (isset($_POST['submit'])) {
                $check = getimagesize($_FILES['fileToUpload']['tmp_name']);
                if ($check !== false) {
                    // echo 'File is an image - ' . $check['mime'] . '.';
                    $uploadOk = 1;
                } else {
                    // echo 'File is not an image.';
                    $uploadOk = 0;
                }
            }

            // Check if file already exists
            if (file_exists($target_file)) {
                // echo 'Sorry, file already exists.';
                $uploadOk = 0;
            }

            // Check file size
            // if ($_FILES['fileToUpload']['size'] > 500000) {
            //     echo 'Sorry, your file is too large.';
            //     $uploadOk = 0;
            // }

            // Allow certain file formats
            if (!in_array($imageFileType, ['jpg','jpeg','png','gif'])) {
                // echo 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.';
                $uploadOk = 0;
            }

            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
                // echo 'Sorry, your file was not uploaded.';
            // if everything is ok, try to upload file
            } else {
                if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $target_file)) {
                    // echo 'The file '. basename( $_FILES['fileToUpload']['name']). ' has been uploaded.';
                } else {
                    // echo 'Sorry, there was an error uploading your file.';
                }
            }
        }

        $this->redirect('Files:default');
        // $this->terminate(); // Maybe better ?
    }
}
