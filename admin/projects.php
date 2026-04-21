<?php
// admin/projects.php
ob_start();
require_once '../config/database.php';
require_once '../models/Project.php';
require_once '../models/ProjectTeam.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$projectModel = new Project($db);
$teamModel = new ProjectTeam($db);

// Handle status update
if (isset($_GET['update_status']) && isset($_GET['id']) && isset($_GET['status'])) {
    $projectModel->updateStatus($_GET['id'], $_GET['status']);
    header("Location: projects.php?msg=status_updated");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    if ($projectModel->delete($_GET['delete'])) {
        header("Location: projects.php?msg=deleted");
        exit();
    }
}

// Get all projects
$projects = $projectModel->getAllWithDetails();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Projects</h2>
    <a href="project-create.php" class="btn btn-dark">
        <i class="bi bi-plus-circle"></i> Add New Project
    </a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        if ($_GET['msg'] == 'created') echo "Project created successfully!";
        if ($_GET['msg'] == 'updated') echo "Project updated successfully!";
        if ($_GET['msg'] == 'deleted') echo "Project deleted successfully!";
        if ($_GET['msg'] == 'status_updated') echo "Project status updated successfully!";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Project Code</th>
                        <th>Project Name</th>
                        <th>Bride & Groom</th>
                        <th>Event Date</th>
                        <th>Package</th>
                        <th>Total Amount</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th>Team</th>
                        <th>Invoice</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($projects)): ?>
                        <?php foreach ($projects as $project): 
                            $total_paid = $project['paid_amount'] ?? 0;
                            $due = $project['total_amount'] - $total_paid;
                            $status_class = [
                                'draft' => 'secondary',
                                'active' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger'
                            ][$project['status']] ?? 'secondary';
                        ?>
                            <tr>
                                <td><strong><?php echo $project['project_code']; ?></strong></td>
                                <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($project['bride_name']); ?> &<br>
                                    <?php echo htmlspecialchars($project['groom_name']); ?>
                                </td>
                                <td><?php echo date('d M Y', strtotime($project['event_date'])); ?></td>
                                <td><?php echo htmlspecialchars($project['package_name']); ?></td>
                                <td>৳ <?php echo number_format($project['total_amount'], 2); ?></td>
                                <td class="text-success">৳ <?php echo number_format($total_paid, 2); ?></td>
                                <td class="<?php echo $due > 0 ? 'text-danger' : 'text-success'; ?>">
                                    ৳ <?php echo number_format($due, 2); ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $status_class; ?>">
                                        <?php echo ucfirst($project['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $project['team_count']; ?> members</span>
                                </td>
                                <td>
                                    <?php if ($project['invoice_sent']): ?>
                                        <span class="badge bg-success">Sent</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="project-view.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View Project">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="project-edit.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit Project">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-warning dropdown-toggle" data-bs-toggle="dropdown">
                                            Status
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="?update_status=1&id=<?php echo $project['id']; ?>&status=draft">Draft</a></li>
                                            <li><a class="dropdown-item" href="?update_status=1&id=<?php echo $project['id']; ?>&status=active">Active</a></li>
                                            <li><a class="dropdown-item" href="?update_status=1&id=<?php echo $project['id']; ?>&status=completed">Completed</a></li>
                                            <li><a class="dropdown-item" href="?update_status=1&id=<?php echo $project['id']; ?>&status=cancelled">Cancelled</a></li>
                                        </ul>
                                    </div>
                                 </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" class="text-center">No projects found. <a href="project-create.php">Create your first project</a> </tr>
                    <?php endif; ?>
                </tbody>
             </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
ob_end_flush();
?>