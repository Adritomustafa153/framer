<?php
// admin/activity.php
ob_start();
require_once '../config/database.php';
require_once '../models/ActivityLog.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$activityLog = new ActivityLog($db);

// Date filter
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

$activities = $activityLog->getByDateRange($start_date, $end_date);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Activity Log</h2>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-dark">
                    <i class="bi bi-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                     <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Table</th>
                        <th>Record ID</th>
                        <th>Description</th>
                        <th>IP Address</th>
                      </tr>
                </thead>
                <tbody>
                    <?php if (!empty($activities)): ?>
                        <?php foreach ($activities as $activity): ?>
                              <tr>
                                <td><?php echo date('M d, Y H:i:s', strtotime($activity['created_at'])); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($activity['username'] ?? 'System'); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($activity['full_name'] ?? ''); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $activity['action'] == 'create' ? 'success' : 
                                            ($activity['action'] == 'update' ? 'info' : 
                                            ($activity['action'] == 'delete' ? 'danger' : 'secondary')); 
                                    ?>">
                                        <?php echo ucfirst($activity['action']); ?>
                                    </span>
                                </td>
                                <td><?php echo ucfirst($activity['table_name'] ?? '-'); ?></td>
                                <td><?php echo $activity['record_id'] ?? '-'; ?></td>
                                <td><?php echo htmlspecialchars($activity['description'] ?? '-'); ?></td>
                                <td><?php echo $activity['ip_address'] ?? '-'; ?></td>
                              </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                         <tr>
                            <td colspan="7" class="text-center">No activity logs found for this period.</td>
                         </tr>
                    <?php endif; ?>
                </tbody>
             </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
ob_end_flush();
?>