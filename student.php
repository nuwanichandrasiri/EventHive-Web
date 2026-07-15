<?php
session_start();
require 'db.php';

$message = "";
$error = "";

if (isset($_POST['register_student'])) {
    $student_id = $conn->real_escape_string($_POST['student_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $course = $conn->real_escape_string($_POST['course']);

    if (!preg_match('/^[0-9]+$/', $student_id)) {
        $error = "Student ID must contain numbers only.";
    } elseif (!preg_match('/^[a-zA-Z ]+$/', $name)) {
        $error = "Name must contain letters only.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {
        $check = $conn->query("SELECT * FROM students WHERE student_id='$student_id'");
        if ($check->num_rows > 0) {
            $error = "Student ID already registered.";
        } else {
            $conn->query("INSERT INTO students VALUES ('$student_id','$name','$email','$course')");
            $_SESSION['student_id'] = $student_id;
            $_SESSION['student_name'] = $name;
            $message = "Registered successfully! Your ID: $student_id";
        }
    }
}

if (isset($_POST['login_student'])) {
    $student_id = $conn->real_escape_string($_POST['student_id']);
    $check = $conn->query("SELECT * FROM students WHERE student_id='$student_id'");
    if ($check->num_rows > 0) {
        $student = $check->fetch_assoc();
        $_SESSION['student_id'] = $student['student_id'];
        $_SESSION['student_name'] = $student['name'];
    } else {
        $error = "Student ID not found.";
    }
}

if (isset($_GET['logout'])) {
    unset($_SESSION['student_id']);
    unset($_SESSION['student_name']);
    header("Location: student.php");
    exit();
}

if (isset($_POST['register_event']) && isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];
    $event_id = $conn->real_escape_string($_POST['event_id']);
    $event = $conn->query("SELECT * FROM events WHERE event_id='$event_id'")->fetch_assoc();

    if (!$event) {
        $error = "Event not found.";
    } elseif ($event['status'] === 'Completed') {
        $error = "This event is already completed.";
    } elseif ($event['registered_count'] >= $event['capacity']) {
        $error = "This event is full.";
    } else {
        $check = $conn->query("SELECT * FROM registrations WHERE student_id='$student_id' AND event_id='$event_id'");
        if ($check->num_rows > 0) {
            $error = "You are already registered for this event.";
        } else {
            $reg_id = "REG" . rand(1000, 9999);
            $conn->query("INSERT INTO registrations VALUES ('$reg_id','$student_id','$event_id')");
            $new_count = $event['registered_count'] + 1;
            $new_status = ($new_count >= $event['capacity']) ? 'Full' : 'Upcoming';
            $conn->query("UPDATE events SET registered_count=$new_count, status='$new_status' WHERE event_id='$event_id'");
            $message = "Registered for " . htmlspecialchars($event['name']) . "! ID: $reg_id";
        }
    }
}

if (isset($_GET['cancel']) && isset($_SESSION['student_id'])) {
    $reg_id = $conn->real_escape_string($_GET['cancel']);
    $reg = $conn->query("SELECT * FROM registrations WHERE registration_id='$reg_id' AND student_id='{$_SESSION['student_id']}'")->fetch_assoc();
    if ($reg) {
        $conn->query("DELETE FROM registrations WHERE registration_id='$reg_id'");
        $conn->query("UPDATE events SET registered_count=registered_count-1, status='Upcoming' WHERE event_id='{$reg['event_id']}'");
        $message = "Registration cancelled.";
    } else {
        $error = "Registration not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EventHive — Student Panel</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<nav>
  <div class="brand">Event<span>Hive</span></div>
  <div class="nav-links">
    <a href="index.php">Home</a>
    <a href="student.php" class="active">Student Panel</a>
    <a href="admin.php">Admin</a>
    <?php if (isset($_SESSION['student_id'])): ?>
      <a href="student.php?logout=1">Logout</a>
    <?php endif; ?>
  </div>
</nav>

<?php if (!isset($_SESSION['student_id'])): ?>

  <div class="split-layout">

    <!-- Login -->
    <div class="form-card">
      <h2>Welcome Back</h2>
      <p>Login with your campus student ID.</p>
      <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="form-group">
          <label>Campus Student ID</label>
          <input type="text" name="student_id" placeholder="e.g. 2502760" required />
        </div>
        <button type="submit" name="login_student" class="btn btn-primary" style="width:100%">Login</button>
      </form>
    </div>

    <!-- Register -->
    <div class="form-card" style="background:#0a0a0a;">
      <h2>New Student?</h2>
      <p>Create your account to start registering for events.</p>
      <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="form-group">
          <label>Campus Student ID (numbers only)</label>
          <input type="text" name="student_id" placeholder="e.g. 2502760" required />
        </div>
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="name" placeholder="Your full name" required />
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" placeholder="Your email address" required />
        </div>
        <div class="form-group">
          <label>Course</label>
          <input type="text" name="course" placeholder="e.g. BSc Software Engineering" required />
        </div>
        <button type="submit" name="register_student" class="btn btn-outline" style="width:100%">Create Account</button>
      </form>
    </div>

  </div>

<?php else: ?>

  <div class="page-header">
    <h1><?= htmlspecialchars($_SESSION['student_name']) ?></h1>
    <p>Student ID: <?= $_SESSION['student_id'] ?> &nbsp;·&nbsp; <a href="student.php?logout=1" style="color:var(--red); text-decoration:none;">Logout</a></p>
  </div>

  <div style="padding: 0 60px 60px;">

    <?php if ($message): ?>
      <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <!-- Available Events -->
    <div style="margin-bottom:16px;">
      <p class="section-label">What's Going On</p>
      <h2 class="section-title" style="font-size:48px;">Available Events</h2>
    </div>

    <div class="events-grid" style="margin-bottom:60px;">
      <?php
      $events = $conn->query("SELECT * FROM events WHERE status != 'Completed' ORDER BY date ASC");
      if ($events->num_rows > 0):
        while ($e = $events->fetch_assoc()):
          $percent = $e['capacity'] > 0 ? ($e['registered_count'] / $e['capacity']) * 100 : 0;
          $already = $conn->query("SELECT * FROM registrations WHERE student_id='{$_SESSION['student_id']}' AND event_id='{$e['event_id']}'")->num_rows > 0;
      ?>
        <div class="event-card">
          <div class="event-card-image"></div>
          <div class="event-card-body">
            <h3><?= htmlspecialchars($e['name']) ?></h3>
            <p>📅 <?= $e['date'] ?> &nbsp;|&nbsp; <?= $e['type'] ?></p>
            <p>📍 <?= htmlspecialchars($e['location']) ?></p>
            <p><?= htmlspecialchars($e['description']) ?></p>
            <div class="slots-bar">
              <div class="slots-fill" style="width:<?= $percent ?>%"></div>
            </div>
          </div>
          <div class="event-card-footer">
            <span class="slots-text-sm"><?= $e['capacity'] - $e['registered_count'] ?> slots left</span>
            <?php if ($already): ?>
              <span class="tag tag-upcoming">✓ Registered</span>
            <?php elseif ($e['status'] === 'Full'): ?>
              <span class="tag tag-full">Full</span>
            <?php else: ?>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="event_id" value="<?= $e['event_id'] ?>" />
                <button type="submit" name="register_event" class="btn btn-outline btn-sm">Register</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endwhile; else: ?>
        <div class="empty">No events available at the moment.</div>
      <?php endif; ?>
    </div>

    <!-- My Registrations -->
    <div style="margin-bottom:24px;">
      <p class="section-label">Your Activity</p>
      <h2 class="section-title" style="font-size:48px;">My Registrations</h2>
    </div>

    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Reg ID</th>
            <th>Event</th>
            <th>Date</th>
            <th>Type</th>
            <th>Location</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $my_regs = $conn->query("
            SELECT r.registration_id, e.name, e.date, e.type, e.location, e.status
            FROM registrations r
            JOIN events e ON r.event_id = e.event_id
            WHERE r.student_id='{$_SESSION['student_id']}'
            ORDER BY e.date ASC
          ");
          if ($my_regs->num_rows > 0):
            while ($r = $my_regs->fetch_assoc()):
          ?>
          <tr>
            <td style="color:#444;"><?= $r['registration_id'] ?></td>
            <td style="color:white; font-weight:600;"><?= htmlspecialchars($r['name']) ?></td>
            <td><?= $r['date'] ?></td>
            <td><span class="tag tag-<?= strtolower($r['type']) ?>"><?= $r['type'] ?></span></td>
            <td><?= htmlspecialchars($r['location']) ?></td>
            <td><span class="tag tag-<?= strtolower($r['status']) ?>"><?= $r['status'] ?></span></td>
            <td>
              <?php if ($r['status'] !== 'Completed'): ?>
                <a href="student.php?cancel=<?= $r['registration_id'] ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Cancel this registration?')">Cancel</a>
              <?php else: ?>
                <span style="color:#333; font-size:12px; letter-spacing:1px; text-transform:uppercase;">Completed</span>
              <?php endif; ?>
            </td>
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

<?php endif; ?>

<footer>
  <div class="footer-brand">Event<span>Hive</span></div>
  <p>University Event Registration System &copy; <?= date('Y') ?></p>
</footer>

</body>
</html>