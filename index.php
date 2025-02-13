<?php
// Định nghĩa đường dẫn chứa dữ liệu JSON
define('LAZER_DATA_PATH', realpath(__DIR__) . '/data/');

// Nạp thư viện từ Composer
require_once 'vendor/autoload.php';

use Lazer\Classes\Database as Lazer;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\Relation;

// Kiểm tra và xóa bảng users nếu tồn tại
if (Validate::table('users')->exists()) {
    Lazer::remove('users');
    echo "Đã xóa bảng users cũ.\n";
}

// Tạo bảng users
Lazer::create('users', [
    'id' => 'integer',
    'name' => 'string',
    'email' => 'string',
    'role_id' => 'integer'
]);

echo "Bảng 'users' đã được tạo.\n";

// Thêm dữ liệu vào bảng 'users'
$usersData = [
    ['name' => 'Truc', 'email' => 'truc@example.com', 'role_id' => 1],
    ['name' => 'Dung', 'email' => 'dung@example.com', 'role_id' => 2],
    ['name' => 'Duong', 'email' => 'duong@example.com', 'role_id' => 3]
];

foreach ($usersData as $data) {
    $user = Lazer::table('users');
    $user->name = $data['name'];
    $user->email = $data['email'];
    $user->role_id = $data['role_id'];
    $user->save();
}

echo "Đã thêm 3 người dùng.\n";

// Hiển thị danh sách người dùng
$users = Lazer::table('users')->findAll();
echo "Danh sách người dùng:\n";
foreach ($users as $user) {
    echo "- Name: {$user->name}, Email: {$user->email}\n";
}

// Cập nhật email của Dung
$dung = Lazer::table('users')->where('name', '=', 'Dung')->find();
if ($dung->count() > 0) {
    $update = Lazer::table('users')->find(current($dung->asArray())['id']);
    $update->email = 'dung_new@example.com';
    $update->save();
    echo "Đã cập nhật email của Dung.\n";
}

// Xóa người dùng Dung
$dung = Lazer::table('users')->where('name', '=', 'Dung')->find();
if ($dung->count() > 0) {
    Lazer::table('users')->find(current($dung->asArray())['id'])->delete();
    echo "Đã xóa Dung khỏi danh sách.\n";
}

// Kiểm tra và xóa bảng roles nếu tồn tại
if (Validate::table('roles')->exists()) {
    Lazer::remove('roles');
    echo "Đã xóa bảng roles cũ.\n";
}

// Tạo bảng roles
Lazer::create('roles', [
    'id' => 'integer',
    'role_name' => 'string'
]);

echo "Bảng 'roles' đã được tạo.\n";

// Thêm dữ liệu vào bảng 'roles'
$rolesData = [
    ['id' => 1, 'role_name' => 'Admin'],
    ['id' => 2, 'role_name' => 'Nhân viên'],
    ['id' => 3, 'role_name' => 'Nhân viên']
];

foreach ($rolesData as $data) {
    $role = Lazer::table('roles');
    $role->id = $data['id'];
    $role->role_name = $data['role_name'];
    $role->save();
}

echo "Đã thêm 3 vai trò.\n";

// Kiểm tra và thiết lập quan hệ giữa users và roles
try {
    $relations = Lazer::table('users')->relations(); // Lấy danh sách quan hệ
    
    if (!isset($relations['roles'])) { // Kiểm tra nếu chưa có quan hệ với roles
        Relation::table('users')->belongsTo('roles')->localKey('role_id')->foreignKey('id')->setRelation();
        echo "Đã thiết lập quan hệ giữa users và roles.\n";
    }
} catch (Exception $e) {
    echo "Lỗi thiết lập quan hệ: " . $e->getMessage() . "\n";
}

// Hiển thị danh sách người dùng sau khi cập nhật
$users = Lazer::table('users')->findAll();
echo "Danh sách người dùng sau cập nhật:\n";
foreach ($users as $user) {
    $role = Lazer::table('roles')->find($user->role_id);
    echo "- Name: {$user->name}, Email: {$user->email}, Role: {$role->role_name}\n";
}






//Tính năng mới: Sharding và cân bằng tải dữ liệu
// Tạo danh sách các shards
$shards = ['users_shard_1', 'users_shard_2', 'users_shard_3'];

// Hàm chọn shard dựa trên role_id
function selectShard($role_id) {
    global $shards;
    return $shards[$role_id % count($shards)];
}

// Xóa và tạo lại các shards
foreach ($shards as $shard) {
    try {
        if (Validate::table($shard)->exists()) {
            Lazer::remove($shard);
            sleep(1); // Chờ 1 giây để hệ thống cập nhật trạng thái
        }
    } catch (Exception $e) {
        echo "Bảng $shard chưa tồn tại, tạo mới.\n";
    }

    // Tạo bảng shard mới
    try {
        Lazer::create($shard, [
            'id' => 'integer',
            'name' => 'string',
            'email' => 'string',
            'role_id' => 'integer'
        ]);
        echo "Đã tạo bảng $shard.\n";
        
        // Kiểm tra lại sau khi tạo
        if (!Validate::table($shard)->exists()) {
            throw new Exception("Lỗi: Không thể xác nhận bảng $shard đã được tạo.");
        }
    } catch (Exception $e) {
        echo "Lỗi khi tạo bảng $shard: " . $e->getMessage() . "\n";
    }
}


// Thêm dữ liệu vào các shards
$usersData = [
    ['name' => 'Truc', 'email' => 'truc@example.com', 'role_id' => 1],
    ['name' => 'Dung', 'email' => 'dung@example.com', 'role_id' => 2],
    ['name' => 'Duong', 'email' => 'duong@example.com', 'role_id' => 3]
];

foreach ($usersData as $data) {
    $shard = selectShard($data['role_id']);
    $user = Lazer::table($shard);
    $user->name = $data['name'];
    $user->email = $data['email'];
    $user->role_id = $data['role_id'];
    $user->save();
}

echo "Dữ liệu người dùng đã được lưu vào các shards.\n";

// Cân bằng tải đọc dữ liệu từ shard ngẫu nhiên
function getRandomUser() {
    global $shards;
    $randomShard = $shards[array_rand($shards)];
    $users = Lazer::table($randomShard)->findAll();

    if ($users->count() > 0) {
        $usersArray = $users->asArray(); // Chuyển object thành mảng
        $user = $usersArray[array_rand($usersArray)]; // Chọn user ngẫu nhiên từ mảng
        
        echo "Người dùng ngẫu nhiên: {$user['name']}, Email: {$user['email']}\n";
    } else {
        echo "Không có người dùng nào trong shard $randomShard.\n";
    }
}

// Hiển thị danh sách người dùng từ tất cả các shards
echo "\nDanh sách người dùng từ các shards:\n";

foreach ($shards as $shard) {
    $users = Lazer::table($shard)->findAll();
    
    echo "Shard: $shard\n";
    if ($users->count() > 0) {
        foreach ($users as $user) {
            echo "- Name: {$user->name}, Email: {$user->email}, Role ID: {$user->role_id}\n";
        }
    } else {
        echo "- Không có người dùng nào trong shard này.\n";
    }
}

?>