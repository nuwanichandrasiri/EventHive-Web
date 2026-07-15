<?php
session_start();
require 'db.php';

$message = "";
$error = "";

if (isset($_POST['login'])) {
    if ($_POST['password'] === 'admin123') {
        $_SESSION['admin'] = true;
    } else {
        $error = "Incorrect password.";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit();
}

if (isset($_POST['create_event']) && isset($_SESSION['admin'])) {
    $event_id = "EVT" . rand(1000, 9999);
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $date = $conn->real_escape_string($_POST['date']);
    $type = $conn->real_escape_string($_POST['type']);
    $location = $conn->real_escape_string($_POST['location']);
    $capacity = intval($_POST['capacity']);

    $check = $conn->query("SELECT * FROM events WHERE name='$name' AND date='$date'");
    if ($check->num_rows > 0) {
        $error = "An event with the same name and date already exists.";
    } else {
        $conn->query("INSERT INTO events VALUES ('$event_id','$name','$description','$date','$type','$location',$capacity,0,'Upcoming')");
        $message = "Event created successfully! ID: $event_id";
    }
}

if (isset($_GET['complete']) && isset($_SESSION['admin'])) {
    $event_id = $conn->real_escape_string($_GET['complete']);
    $conn->query("UPDATE events SET status='Completed' WHERE event_id='$event_id'");
    $message = "Event marked as Completed.";
}

if (isset($_GET['delete']) && isset($_SESSION['admin'])) {
    $event_id = $conn->real_escape_string($_GET['delete']);
    $conn->query("DELETE FROM registrations WHERE event_id='$event_id'");
    $conn->query("DELETE FROM events WHERE event_id='$event_id'");
    $message = "Event deleted.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EventHive — Admin</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<nav>
  <div class="brand">Event<span>Hive</span></div>
  <div class="nav-links">
    <a href="index.php">Home</a>
    <a href="student.php">Student Panel</a>
    <a href="admin.php" class="active">Admin</a>
    <?php if (isset($_SESSION['admin'])): ?>
      <a href="admin.php?logout=1">Logout</a>
    <?php endif; ?>
  </div>
</nav>

<?php if (!isset($_SESSION['admin'])): ?>

  <div class="login-wrapper">
    <div class="login-box">
      <div class="form-card">
        <h2>Admin Login</h2>
        <p>Enter your password to access the dashboard.</p>
        <?php if ($error): ?>
          <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter admin password" required />
          </div>
          <button type="submit" name="login" class="btn btn-primary" style="width:100%">Login</button>
        </form>
      </div>
    </div>
  </div>

<?php else: ?>

  <div class="page-header">
    <h1>Admin Dashboard</h1>
    <p>Manage events, registrations and students.</p>
  </div>

  <div style="padding: 0 60px 60px;">

    <?php if ($message): ?>
      <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <?php
    $total_events = $conn->query("SELECT COUNT(*) as c FROM events")->fetch_assoc()['c'];
    $total_students = $conn->query("SELECT COUNT(*) as c FROM students")->fetch_assoc()['c'];
    $total_regs = $conn->query("SELECT COUNT(*) as c FROM registrations")->fetch_assoc()['c'];
    $full_events = $conn->query("SELECT COUNT(*) as c FROM events WHERE status='Full'")->fetch_assoc()['c'];
    ?>
    <div class="stats-bar">
      <div class="stat-box">
        <h4><?= $total_events ?></h4>
        <p>Total Events</p>
      </div>
      <div class="stat-box">
        <h4><?= $total_students ?></h4>
        <p>Students</p>
      </div>
      <div class="stat-box">
        <h4><?= $total_regs ?></h4>
        <p>Registrations</p>
      </div>
      <div class="stat-box">
        <h4><?= $full_events ?></h4>
        <p>Full Events</p>
      </div>
    </div>

    <!-- Create Event -->
    <div class="card">
      <h3>Create New Event</h3>
      <form method="POST">
        <div class="form-row">
          <div class="form-group">
            <label>Event Name</label>
            <input type="text" name="name" placeholder="e.g. TechLeads 2026" required />
          </div>
          <div class="form-group">
            <label>Date</label>
            <input type="date" name="date" required />
          </div>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="description" placeholder="Brief description of the event"></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Type</label>
            <select name="type">
              <option value="Physical">Physical</option>
              <option value="Online">Online</option>
            </select>
          </div>
          <div class="form-group">
            <label>Capacity</label>
            <input type="number" name="capacity" placeholder="Max attendees" required />
          </div>
        </div>
        <div class="form-group">
          <label>Location / Link</label>
          <input type="text" name="location" placeholder="Venue or meeting link" required />
        </div>
        <button type="submit" name="create_event" class="btn btn-primary">Create Event</button>
      </form>
    </div>

    <!-- Events Table -->
    <div class="card">
      <h3>All Events</h3>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Date</th>
              <th>Type</th>
              <th>Location</th>
              <th>Slots</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $events = $conn->query("SELECT * FROM events ORDER BY date ASC");
            while ($e = $events->fetch_assoc()):
              $percent = $e['capacity'] > 0 ? ($e['registered_count'] / $e['capacity']) * 100 : 0;
            ?>
            <tr>
              <td style="color:#444;"><?= $e['event_id'] ?></td>
              <td style="color:white; font-weight:600;"><?= htmlspecialchars($e['name']) ?></td>
              <td><?= $e['date'] ?></td>
              <td><span class="tag tag-<?= strtolower($e['type']) ?>"><?= $e['type'] ?></span></td>
              <td><?= htmlspecialchars($e['location']) ?></td>
              <td>
                <?= $e['registered_count'] ?>/<?= $e['capacity'] ?>
                <div class="slots-bar" style="margin-top:6px;">
                  <div class="slots-fill" style="width:<?= $percent ?>%; background:var(--red);"></div>
                </div>
              </td>
              <td><span class="tag tag-<?= strtolower($e['status']) ?>"><?= $e['status'] ?></span></td>
              <td>
                <?php if ($e['status'] !== 'Completed'): ?>
                  <a href="admin.php?complete=<?= $e['event_id'] ?>" class="btn btn-secondary btn-sm">Complete</a>
                <?php endif; ?>
                <a href="admin.php?delete=<?= $e['event_id'] ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Delete this event?')">Delete</a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Registrations Table -->
    <div class="card">
      <h3>All Registrations</h3>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Reg ID</th>
              <th>Student</th>
              <th>Student ID</th>
              <th>Course</th>
              <th>Event</th>
              <th>Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $regs = $conn->query("
              SELECT r.registration_id, s.name, s.student_id, s.course,
                     e.name as event_name, e.date, e.status
              FROM registrations r
              JOIN students s ON r.student_id = s.student_id
              JOIN events e ON r.event_id = e.event_id
              ORDER BY e.date ASC
            ");
            if ($regs->num_rows > 0):
              while ($r = $regs->fetch_assoc()):
            ?>
            <tr>
              <td style="color:#444;"><?= $r['registration_id'] ?></td>
              <td style="color:white; font-weight:600;"><?= htmlspecialchars($r['name']) ?></td>
              <td><?= $r['student_id'] ?></td>
              <td><?= htmlspecialchars($r['course']) ?></td>
              <td><?= htmlspecialchars($r['event_name']) ?></td>
              <td><?= $r['date'] ?></td>
              <td><span class="tag tag-<?= strtolower($r['status']) ?>"><?= $r['status'] ?></span></td>
            </tr>
            <?php endwhile; else: ?>
            <tr>
              <td colspan="7" style="text-align:center; color:#444; padding:40px; letter-spacing:2px; text-transform:uppercase; font-size:12px;">
                No registrations yet
              </td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

<?php endif; ?>

<footer>
  <div class="footer-brand">Event<span>Hive</span></div>
  <p>University Event Registration System &copy; <?= date('Y') ?></p>
</footer>

</body>
</html>