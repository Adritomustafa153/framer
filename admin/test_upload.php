<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo '<pre>';
    echo 'POST data: ';
    print_r($_POST);
    echo "\nFILES data: ";
    print_r($_FILES);
    echo '</pre>';
    if (isset($_FILES['test']) && $_FILES['test']['error'] === UPLOAD_ERR_OK) {
        move_uploaded_file($_FILES['test']['tmp_name'], '../uploads/test_' . $_FILES['test']['name']);
        echo 'File saved successfully.';
    } else {
        echo 'Upload error code: ' . ($_FILES['test']['error'] ?? 'no file');
    }
    exit;
}
?>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="test">
    <button type="submit">Upload</button>
</form>