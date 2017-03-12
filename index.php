<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/include/notes_config.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';
sec_session_start();
if(token_check($mysqli) != true){
    if(login_check($mysqli) != true) {
        header('Location: ../login?error=2');
        exit();
    }
}

// Core (class)
class Notes {
    
    private $mysqli;
    private $user;

    function __construct($mysqli, $user) {
        $this->mysqli = $mysqli;
        $this->user = $user;

        // Create new table if not exists.
        if($stmt=$this->mysqli->prepare('CREATE TABLE IF NOT EXISTS '.$this->user.' (ID INTEGER PRIMARY KEY AUTO_INCREMENT, title TEXT NOT NULL, content TEXT NOT NULL, created DATETIME NOT NULL);')){
            $stmt->execute();
        }
    }

    public function fetchNotes($id = null) {
        if ($id != null) {
            if($stmt=$this->mysqli->prepare('SELECT title,content FROM '.$this->user.' WHERE id = ?')){
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $result = $stmt->get_result();
                foreach($result as $row){
                    $title = $row['title'];
                    header("Content-type: text/plain; charset=utf-8");
                    header("Content-Disposition: attachment; filename=$title.txt");
                    echo $row['content'];
                    return;
                }
            }
        } else {
            if($stmt=$this->mysqli->prepare('SELECT * FROM '.$this->user.' ORDER BY created DESC')){
                $stmt->execute();
                $result = $stmt->get_result();
                return $result;
            }
        }
    }

    public function create($title, $content) {
        $datetime = date("Y-m-d H:i:s");
        if($stmt=$this->mysqli->prepare('INSERT INTO '.$this->user.' (title, content, created) VALUES (?, ?, ?)')){
            $stmt->bind_param('sss',$title, $content, $datetime);
            $stmt->execute();
        }
    }

    public function delete($id) {
        if ($id == 'all') {
            $stmt = $this->mysqli->query('DROP table '.$this->user.' FROM notes; VACUUM');
             __construct();
        } else {
            if($stmt=$this->mysqli->prepare('DELETE FROM '.$this->user.' WHERE id = ?')){
                $stmt->bind_param('i', $id);
                $stmt->execute();
            }
        }
    }

    public function edit($id, $title, $content) {
        if($stmt=$this->mysqli->prepare('UPDATE '.$this->user.' SET title = ?, content = ? WHERE id = ?')){
            $stmt->bind_param('ssi',$title, $content, $id);
            $stmt->execute();
        }
    }
}

// Init core (class)
$notes = new Notes($mysqli_notes, $_SESSION['username']);

// Actions
if (isset($_POST['new'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $notes->create($title, $content);
}
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $notes->edit($id, $title, $content);
}
if (!empty($_GET['del'])) {
    $id = $_GET['del'];
    $notes->delete($id);
}
if (!empty($_GET['dl'])) {
    $id = $_GET['dl'];
    $notes->fetchNotes($id);
    exit();
}

?>
<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title> Simple Note - <?php echo $_SESSION['username'];?></title>

    <link rel="stylesheet" href="//bootswatch.com/flatly/bootstrap.css">

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>

    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

    <script src="notes.js"></script>

    <style>
        .container {
            max-width: 680px;
        }
        
        textarea {                
            resize: vertical;    /* allow only vertical stretch */
        }
    </style>

</head>

<body>

    <div class="container">

        <div class="page-header">
            <h2> Send a new note </h2>
        </div>

        <form role="form" action="index" method="POST">
            <div class="form-group">
                <input class="form-control" type="text" placeholder="Title" name="title" required>
            </div>
            <div class="form-group">
                <textarea class="form-control" rows="5" placeholder="What do you have in mind ?" name="content" autofocus required></textarea>
            </div>
            <div class="btn-group pull-right">
                <button class="btn btn-danger" type="reset"><span class="glyphicon glyphicon-remove"></span> Clear </button>
                <button class="btn btn-success" name="new" type="submit"><span class="glyphicon glyphicon-send"></span> Send </button>
            </div>
        </form>
    </div>

    <?php
    if (!empty($notes->fetchNotes())):
        $notes = $notes->fetchNotes();
    ?>

    <div class="container" id="notes">
        <div class="page-header">
            <h2> Previously sent </h2>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Time</th>
                            <th>Date</th>
                            <th class="pull-right">Actions<br></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
<?php foreach ($notes as $row): ?>
                            <td>
                                <small><?= htmlspecialchars(substr($row['title'], 0, 15), ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td><?= date('H:i', strtotime($row['created'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($row['created'])) ?></td>
                            <td class="pull-right">
                                <div class="btn-group">
                                    <a class="btn btn-default btn-xs" title="Edit this note" href="#" data-toggle="modal" data-target="#<?= $row['ID'] ?>"><span class="glyphicon glyphicon-edit"></span></a>
                                    <a class="btn btn-danger btn-xs" title="Delete this note" onclick="confirm_Delete(<?= $row['ID'] ?>)" ><span class="glyphicon glyphicon-trash"></span></a>
                                    <a class="btn btn-info btn-xs" title="Download this note" href="?dl=<?= $row['ID'] ?>" target="_blank"><span class="glyphicon glyphicon-download-alt"></span></a>
                                </div>
                            </td>
                        </tr>
                        <div class="modal fade" id="<?= $row['ID'] ?>" role="dialog">
                            <div class="modal-dialog modal-lg">
                              <div class="modal-content">
                                <div class="modal-header">
                                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                                  <h4 class="modal-title">Edit note</h4>
                                </div>
                                <div class="modal-body">
                                  <form role="form" action="index.php" method="POST">
                                    <div class="form-group">
                                        <input class="form-control" type="text" placeholder="Title" name="title" value="<?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                    <div class="form-group">
                                        <textarea class="form-control" rows="5" placeholder="What do you have in mind ?" name="content" required><?= htmlspecialchars($row['content'], ENT_QUOTES, 'UTF-8') ?></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <input type="hidden" name="id" value="<?= $row['ID'] ?>">
                                    <div class="btn-group pull-right">
                                        <button class="btn btn-success" name="edit" type="submit"><span class="glyphicon glyphicon-floppy-disk"></span> Save </button>
                                    </div>
                                </div>
                                </form>
                              </div>
                            </div>
                        </div>
<?php endforeach; ?>
                    </tbody>
            </table>
        </div>
<?php endif; ?>
    </div>

</body>

</html>
