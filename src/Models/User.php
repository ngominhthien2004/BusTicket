<?php

namespace Ct27501Project\Models;

class User
{
    private $pdo;

    // Properties mapping to users table
    public $user_id;
    public $email;
    public $password;
    public $full_name;
    public $phone_number;
    public $facebook_id;
    public $google_id;
    public $role;
    public $created_at;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?: PDO();
    }

    public function where(string $column, string $value): User
    {
        $statement = $this->pdo->prepare("select * from users where $column = :value");
        $statement->execute(['value' => $value]);
        $row = $statement->fetch();
        if ($row) {
            $this->fillFromDbRow($row);
        }
        return $this;
    }

    private function fillFromDbRow(array $row)
    {
        $this->user_id = $row['user_id'];
        $this->email = $row['email'];
        $this->full_name = $row['full_name'];
        $this->password = $row['password'];
        $this->phone_number = $row['phone_number'];
        $this->facebook_id = $row['facebook_id'] ?? '';
        $this->google_id = $row['google_id'] ?? '';
        $this->role = $row['role'] ?? 'user';
        $this->created_at = $row['created_at'] ?? date('Y-m-d H:i:s');
    }

    private function isEmailInUse(string $email): bool
    {
        $statement = $this->pdo->prepare('select count(*) from users where email = :email');
        $statement->execute(['email' => $email]);
        return $statement->fetchColumn() > 0;
    }

    private function isPhoneNumberIsUse(string $phone_number): bool
    {
        $statement = $this->pdo->prepare('select count(*) from users where phone_number = :phone_number');
        $statement->execute(['phone_number' => $phone_number]);
        return $statement->fetchColumn() > 0;
    }

    public function validate(array $data): array
    {
        $errors = [];

        if (!$data['full_name']) {
            $errors['full_name'] = 'Trường họ và tên là bắt buộc';
        }

        if (!$data['email']) {
            $errors['email'] = 'Invalid email.';
        } elseif ($this->isEmailInUse($data['email'])) {
            $errors['email'] = 'Email already in use.';
        }

        $validPhone = preg_match(
            '/^(03|05|07|08|09|01[2|6|8|9])+([0-9]{8})\b$/',
            $data['phone_number'] ?? ''
        );
        if (!$validPhone) {
            $errors['phone_number'] = 'Invalid phone number.';
        }

        if (!$data['phone_number']) {
            $errors['phone_number'] = 'Invalid phone number';
        } elseif ($this->isPhoneNumberIsUse($data['phone_number'])) {
            $errors['phone_number'] = 'Phone number already in use.';
        }

        if (strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters.';
        } elseif ($data['password'] != $data['password_confirmation']) {
            $errors['password'] = 'Password confirmation does not match.';
        }

        return $errors;
    }

    public function fill(array $data): User
    {
        $this->email = $data['email'];
        $this->full_name = $data['full_name'] ?? '';
        if (!empty($data['password'])) {
            $this->password = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $this->phone_number = $data['phone_number'] ?? '';
        $this->facebook_id = $data['facebook_id'] ?? '';
        $this->google_id = $data['google_id'] ?? '';
        $this->role = $data['role'] ?? 'user';
        $this->created_at = date('Y-m-d H:i:s');
        return $this;
    }

    public function save(): bool
    {
        $result = false;

        if (is_numeric($this->user_id) && $this->user_id > 0) {
            $statement = $this->pdo->prepare(
                'update users set email = :email, full_name = :full_name, password = :password,
            phone_number = :phone_number, facebook_id = :facebook_id, google_id = :google_id, role = :role where user_id = :user_id'
            );
            $result = $statement->execute([
                'user_id' => $this->user_id,
                'email' => $this->email,
                'full_name' => $this->full_name,
                'password' => $this->password,
                'phone_number' => $this->phone_number,
                'facebook_id' => $this->facebook_id,
                'google_id' => $this->google_id,
                'role' => $this->role,
            ]);
        } else {
            $statement = $this->pdo->prepare(
                'insert into users (email, full_name, password, phone_number, facebook_id, google_id, role, created_at)
                values (:email, :full_name, :password, :phone_number, :facebook_id, :google_id, :role, now())'
            );
            $result = $statement->execute([
                'email' => $this->email,
                'full_name' => $this->full_name,
                'password' => $this->password,
                'phone_number' => $this->phone_number,
                'facebook_id' => $this->facebook_id,
                'google_id' => $this->google_id,
                'role' => $this->role,
            ]);
            if ($result) {
                $this->user_id = (int)$this->pdo->lastInsertId();
            }
        }

        return $result;
    }

    public function findOrCreateByGoogle(array $googleUser): User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE google_id = :google_id');
        $stmt->execute(['google_id' => $googleUser['id']]);
        $row = $stmt->fetch();

        if ($row) {
            $this->fillFromDbRow($row);
            return $this;
        }

        if (!empty($googleUser['email'])) {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute(['email' => $googleUser['email']]);
            $row = $stmt->fetch();

            if ($row) {
                $this->fillFromDbRow($row);
                $this->google_id = $googleUser['id'];
                $this->save();
                return $this;
            }
        }

        $this->email = $googleUser['email'] ?? '';
        $this->full_name = $googleUser['name'] ?? '';
        $this->google_id = $googleUser['id'];
        $this->password = '';
        $this->phone_number = '';
        $this->facebook_id = '';
        $this->created_at = date('Y-m-d H:i:s');
        $this->save();

        return $this;
    }

    public function findOrCreateByFacebook(array $facebookUser): User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE facebook_id = :facebook_id');
        $stmt->execute(['facebook_id' => $facebookUser['id']]);
        $row = $stmt->fetch();

        if ($row) {
            $this->fillFromDbRow($row);
            return $this;
        }

        if (!empty($facebookUser['email'])) {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute(['email' => $facebookUser['email']]);
            $row = $stmt->fetch();

            if ($row) {
                $this->fillFromDbRow($row);
                $this->facebook_id = $facebookUser['id'];
                $this->save();
                return $this;
            }
        }

        $this->email = $facebookUser['email'] ?? '';
        $this->full_name = $facebookUser['name'] ?? '';
        $this->facebook_id = $facebookUser['id'];
        $this->password = '';
        $this->phone_number = '';
        $this->google_id = '';
        $this->created_at = date('Y-m-d H:i:s');
        $this->save();

        return $this;
    }

    /**
     * Tìm user theo ID
     */
    public static function find($id)
    {
        $user = new self();
        $stmt = $user->pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $user->fillFromArray($data);
            return $user;
        }
        return null;
    }

    //get all users
    public function getAllUsers()
    {
        $stmt = $this->pdo->query("SELECT * FROM users");
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $users;
    }

    public function searchUsers($filters = [])
    {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];

        // Tìm theo email
        if (!empty($filters['email'])) {
            $sql .= " AND email LIKE ?";
            $params[] = '%' . $filters['email'] . '%';
        }

        // Tìm theo số điện thoại
        if (!empty($filters['phone_number'])) {
            $sql .= " AND phone_number LIKE ?";
            $params[] = '%' . $filters['phone_number'] . '%';
        }

        if (!empty($filters['role'])) {
            $sql .= " AND role = ?";
            $params[] = $filters['role'];
        }
        $sql .= " ORDER BY created_at DESC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error searching users: " . $e->getMessage());
            return [];
        }
    }


    /**
     * Lấy tất cả users
     */
    public static function all($limit = null, $offset = 0)
    {
        $user = new self();
        $sql = "SELECT * FROM users ORDER BY created_at DESC";

        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $user->pdo->query($sql);
        $users = [];

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $userObj = new self($user->pdo);
            $userObj->fillFromArray($data);
            $users[] = $userObj;
        }

        return $users;
    }

    /**
     * Tạo user mới
     */
    public function create($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (email, password, full_name, phone_number, facebook_id, google_id, role, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");

        $result = $stmt->execute([
            $data['email'],
            $data['password'] ?? null,
            $data['full_name'],
            $data['phone_number'] ?? null,
            $data['facebook_id'] ?? null,
            $data['google_id'] ?? null,
            $data['role'] ?? 'user'
        ]);

        if ($result) {
            $this->user_id = $this->pdo->lastInsertId();
        }

        return $result;
    }

    /**
     * Cập nhật user
     */
    public function update($data)
    {
        $fields = [];
        $values = [];

        $allowedFields = ['email', 'password', 'full_name', 'phone_number', 'role'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $this->user_id;

        $stmt = $this->pdo->prepare("
            UPDATE users SET " . implode(', ', $fields) . "
            WHERE user_id = ?
        ");

        return $stmt->execute($values);
    }

    /**
     * Xóa user
     */
    public function delete($userId)
    {
        $sql = "DELETE FROM users WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId]);
    }

    /**
     * Kiểm tra mật khẩu
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * Hash mật khẩu
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Tìm kiếm users
     */
    public static function search($conditions, $limit = null, $offset = 0)
    {
        $user = new self();
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];

        if (!empty($conditions['email'])) {
            $sql .= " AND email LIKE ?";
            $params[] = "%{$conditions['email']}%";
        }

        if (!empty($conditions['full_name'])) {
            $sql .= " AND full_name LIKE ?";
            $params[] = "%{$conditions['full_name']}%";
        }

        if (!empty($conditions['role'])) {
            $sql .= " AND role = ?";
            $params[] = $conditions['role'];
        }

        $sql .= " ORDER BY created_at DESC";

        if ($limit) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $user->pdo->prepare($sql);
        $stmt->execute($params);
        $users = [];

        while ($data = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $userObj = new self($user->pdo);
            $userObj->fillFromArray($data);
            $users[] = $userObj;
        }

        return $users;
    }

    /**
     * Điền dữ liệu từ mảng
     */
    private function fillFromArray($data)
    {
        $this->user_id = $data['user_id'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->full_name = $data['full_name'] ?? null;
        $this->phone_number = $data['phone_number'] ?? null;
        $this->facebook_id = $data['facebook_id'] ?? null;
        $this->google_id = $data['google_id'] ?? null;
        $this->role = $data['role'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
    }

    /**
     * Chuyển đổi thành mảng (ẩn password)
     */
    public function toArray($includePassword = false)
    {
        $array = [
            'user_id' => $this->user_id,
            'email' => $this->email,
            'full_name' => $this->full_name,
            'phone_number' => $this->phone_number,
            'facebook_id' => $this->facebook_id,
            'google_id' => $this->google_id,
            'role' => $this->role,
            'created_at' => $this->created_at
        ];

        if ($includePassword) {
            $array['password'] = $this->password;
        }

        return $array;
    }
}
