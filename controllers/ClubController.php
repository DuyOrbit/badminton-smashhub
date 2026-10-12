<?php
class ClubController {
    public function index() {
        $database = new Database();
        $db = $database->getConnection();

        $clubModel = new Club($db);
        $pitchModel = new Pitch($db);

        $keyword = trim($_GET['q'] ?? '');
        $district = $_GET['district'] ?? '';
        $recruiting = isset($_GET['recruiting']) ? $_GET['recruiting'] : '';

        $clubs = $clubModel->readAll($keyword, $district, $recruiting);

        // Danh sách quận từ cụm sân
        $stmt_pitches = $pitchModel->readAll();
        $all_pitches = $stmt_pitches->fetchAll(PDO::FETCH_ASSOC);
        $districts = [];
        foreach ($all_pitches as $p) {
            if (!empty($p['district']) && !in_array($p['district'], $districts)) {
                $districts[] = $p['district'];
            }
        }
        sort($districts);

        // CLB mà user đang tham gia
        $my_clubs = [];
        if (isset($_SESSION['user_id'])) {
            $my_clubs = $clubModel->getUserClubs($_SESSION['user_id']);
        }

        $page_title = "Cộng Đồng Câu Lạc Bộ Cầu Lông";
        $content = 'clubs/index.php';
        require_once 'views/layout.php';
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        $pitchModel = new Pitch($db);

        $pitches = $pitchModel->readAll()->fetchAll(PDO::FETCH_ASSOC);

        $page_title = "Tạo Câu Lạc Bộ Mới";
        $content = 'clubs/create.php';
        require_once 'views/layout.php';
    }

    public function store() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $db = $database->getConnection();
            $clubModel = new Club($db);

            $name = trim($_POST['name'] ?? '');
            if (empty($name)) {
                header("Location: index.php?action=club_create&error=name_required");
                exit;
            }

            $data = [
                'name' => $name,
                'description' => $_POST['description'] ?? '',
                'leader_id' => $_SESSION['user_id'],
                'pitch_id' => !empty($_POST['pitch_id']) ? $_POST['pitch_id'] : null,
                'schedule_info' => $_POST['schedule_info'] ?? '',
                'district' => $_POST['district'] ?? '',
                'level_requirement' => $_POST['level_requirement'] ?? 'Mọi trình độ (Giao lưu học hỏi)',
                'is_recruiting' => isset($_POST['is_recruiting']) ? 1 : 0
            ];

            $club_id = $clubModel->create($data);
            if ($club_id) {
                header("Location: index.php?action=club_detail&id=$club_id&msg=created");
            } else {
                header("Location: index.php?action=club_create&error=create_failed");
            }
            exit;
        }
        header("Location: index.php?action=clubs");
        exit;
    }

    public function detail() {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            header("Location: index.php?action=clubs");
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();
        $clubModel = new Club($db);

        $club = $clubModel->readOne($id);
        if (!$club) {
            header("Location: index.php?action=clubs&error=not_found");
            exit;
        }

        $members = $clubModel->getMembers($id);
        $posts = $clubModel->getPosts($id);

        $user_id = $_SESSION['user_id'] ?? null;
        $my_membership = $clubModel->getUserMembership($id, $user_id);
        $is_leader = ($my_membership && $my_membership['role'] === 'leader');
        $is_member = ($my_membership && $my_membership['status'] === 'approved');

        $page_title = $club['name'] . " - Chi Tiết CLB";
        $content = 'clubs/detail.php';
        require_once 'views/layout.php';
    }

    public function join() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }

        $club_id = (int)($_POST['club_id'] ?? $_GET['id'] ?? 0);
        if ($club_id) {
            $database = new Database();
            $db = $database->getConnection();
            $clubModel = new Club($db);

            if ($clubModel->joinClub($club_id, $_SESSION['user_id'])) {
                header("Location: index.php?action=club_detail&id=$club_id&msg=joined");
                exit;
            }
        }
        header("Location: index.php?action=club_detail&id=$club_id");
        exit;
    }

    public function leave() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }

        $club_id = (int)($_POST['club_id'] ?? $_GET['id'] ?? 0);
        if ($club_id) {
            $database = new Database();
            $db = $database->getConnection();
            $clubModel = new Club($db);

            if ($clubModel->leaveClub($club_id, $_SESSION['user_id'])) {
                header("Location: index.php?action=club_detail&id=$club_id&msg=left");
                exit;
            }
        }
        header("Location: index.php?action=club_detail&id=$club_id");
        exit;
    }

    public function storePost() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $club_id = (int)($_POST['club_id'] ?? 0);
            $content = trim($_POST['content'] ?? '');
            $title = trim($_POST['title'] ?? '');
            $post_type = $_POST['post_type'] ?? 'general';
            $slot_needed = (int)($_POST['slot_needed'] ?? 0);
            $fee_per_slot = (int)($_POST['fee_per_slot'] ?? 0);

            if ($club_id && !empty($content)) {
                $database = new Database();
                $db = $database->getConnection();
                $clubModel = new Club($db);

                $clubModel->createPost($club_id, $_SESSION['user_id'], $title, $content, $post_type, $slot_needed, $fee_per_slot);
                header("Location: index.php?action=club_detail&id=$club_id&msg=posted");
                exit;
            }
            header("Location: index.php?action=club_detail&id=$club_id&error=content_empty");
            exit;
        }
        header("Location: index.php?action=clubs");
        exit;
    }

    public function toggleRecruitment() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }

        $club_id = (int)($_POST['club_id'] ?? $_GET['id'] ?? 0);
        if ($club_id) {
            $database = new Database();
            $db = $database->getConnection();
            $clubModel = new Club($db);

            $clubModel->toggleRecruitment($club_id, $_SESSION['user_id']);
            header("Location: index.php?action=club_detail&id=$club_id&msg=status_updated");
            exit;
        }
        header("Location: index.php?action=clubs");
        exit;
    }
}
?>
