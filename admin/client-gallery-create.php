<?php
// admin/client-gallery-create.php
require_once '../config/database.php';
require_once '../models/ClientGallery.php';
require_once '../models/Project.php';
require_once '../models/Client.php';
require_once 'header.php';

$db = (new Database())->getConnection();
$galleryModel = new ClientGallery($db);
$projectModel = new Project($db);
$clientModel = new Client($db);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'project_id' => $_POST['project_id'] ?: null,
        'client_id'  => $_POST['client_id'] ?: null,
        'title'      => trim($_POST['title']),
        'headline'   => trim($_POST['headline']),
        'story'      => trim($_POST['story']),
        'is_active'  => isset($_POST['is_active']) ? 1 : 0,
        'password'   => !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null
    ];
    if (empty($data['title'])) {
        $error = "Title is required.";
    } else {
        if ($galleryModel->create($data)) {
            $success = "Gallery created.";
        } else {
            $error = "Creation failed.";
        }
    }
}

$projects = $projectModel->getAll();
$clients = $clientModel->getAll();
?>

<h2>Create New Client Gallery</h2>
<?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<form method="POST">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label>Project (optional)</label>
            <select name="project_id" class="form-control">
                <option value="">-- None --</option>
                <?php foreach ($projects as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['project_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label>Client (optional)</label>
            <select name="client_id" class="form-control">
                <option value="">-- None --</option>
                <?php foreach ($clients as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['bride_name'] . ' & ' . $c['groom_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="mb-3"><label>Title *</label><input type="text" name="title" class="form-control" required></div>
    <div class="mb-3"><label>Headline (optional)</label><input type="text" name="headline" class="form-control"></div>
    <div class="mb-3"><label>Short Story</label><textarea name="story" rows="4" class="form-control"></textarea></div>
    <div class="mb-3"><label>Password (optional)</label><input type="text" name="password" class="form-control" autocomplete="off"></div>
    <div class="mb-3 form-check"><input type="checkbox" name="is_active" class="form-check-input" id="is_active" checked> <label class="form-check-label" for="is_active">Active</label></div>
    <button type="submit" class="btn btn-dark">Create Gallery</button>
</form>
<?php require_once 'footer.php'; ?>