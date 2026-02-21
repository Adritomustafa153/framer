<?php
// admin/messages.php
require_once '../config/database.php';
require_once '../models/Message.php';
require_once 'header.php';

$database = new Database();
$db = $database->getConnection();
$message = new Message($db);

// Handle mark as read
if (isset($_GET['read'])) {
    $message->markAsRead($_GET['read']);
    header("Location: messages.php");
    exit();
}

// Handle mark as replied
if (isset($_GET['reply']) && isset($_GET['id'])) {
    $message->markAsReplied($_GET['id'], getCurrentUserId());
    header("Location: messages.php");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $message->delete($_GET['delete']);
    header("Location: messages.php?msg=deleted");
    exit();
}

// Get all messages
$messages = $message->getAll('created_at DESC');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Contact Messages</h2>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Message deleted successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($messages->rowCount() > 0): ?>
                        <?php while ($row = $messages->fetch()): ?>
                            <tr class="<?php echo !$row['is_read'] ? 'table-warning' : ''; ?>">
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><a href="mailto:<?php echo $row['email']; ?>"><?php echo $row['email']; ?></a></td>
                                <td><?php echo htmlspecialchars($row['phone'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['subject'] ?? '-'); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#messageModal<?php echo $row['id']; ?>">
                                        View
                                    </button>
                                </td>
                                <td>
                                    <?php if (!$row['is_read']): ?>
                                        <span class="badge bg-warning">Unread</span>
                                    <?php endif; ?>
                                    <?php if ($row['is_replied']): ?>
                                        <span class="badge bg-success">Replied</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <?php if (!$row['is_read']): ?>
                                        <a href="messages.php?read=<?php echo $row['id']; ?>" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Mark as Read">
                                            <i class="bi bi-check2-circle"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!$row['is_replied']): ?>
                                        <a href="mailto:<?php echo $row['email']; ?>?subject=Re: <?php echo urlencode($row['subject'] ?? 'Your Inquiry'); ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Reply via Email">
                                            <i class="bi bi-reply"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="messages.php?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this message?')"
                                       data-bs-toggle="tooltip" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            
                            <!-- Message Modal -->
                            <div class="modal fade" id="messageModal<?php echo $row['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Message from <?php echo htmlspecialchars($row['name']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Email:</strong> <?php echo $row['email']; ?></p>
                                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></p>
                                            <p><strong>Subject:</strong> <?php echo htmlspecialchars($row['subject'] ?? 'No Subject'); ?></p>
                                            <p><strong>Message:</strong></p>
                                            <div class="border p-3 bg-light">
                                                <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                                            </div>
                                            <p class="mt-3"><small>Received: <?php echo date('F d, Y \a\t h:i A', strtotime($row['created_at'])); ?></small></p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <a href="mailto:<?php echo $row['email']; ?>?subject=Re: <?php echo urlencode($row['subject'] ?? 'Your Inquiry'); ?>" class="btn btn-primary">
                                                <i class="bi bi-reply"></i> Reply
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No messages found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>