<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    /** Role every new sign-up gets (roles.role_id for 'Student'). */
    public const STUDENT_ROLE_ID = 7;

    /**
     * Roles that use the Student Council (SC) side, highest first.
     * If a user has several, the first one found here is shown in the page header.
     */
    public const COUNCIL_ROLES = [
        'Admin',
        'Repository Manager',
        'President',
        'Vice-President',
        'Secretary',
        'Technical Committee',
    ];

    /** @param list<string> $roles */
    public static function isCouncil(array $roles): bool
    {
        return array_intersect($roles, self::COUNCIL_ROLES) !== [];
    }

    /** @param list<string> $roles */
    public static function primaryCouncilRole(array $roles): ?string
    {
        foreach (self::COUNCIL_ROLES as $role) {
            if (in_array($role, $roles, true)) {
                return $role;
            }
        }

        return null;
    }

    /**
     * Page a signed-in user belongs on (path for base_url()).
     *
     * @param list<string> $roles
     */
    public static function homeFor(array $roles): string
    {
        return self::isCouncil($roles) ? 'dashboard' : 'student';
    }

    protected $table         = 'users';
    protected $primaryKey    = 'user_id';
    protected $returnType    = 'array';
    protected $allowedFields = ['first_name', 'Last_name', 'nickname', 'email', 'password'];

    // date_registered / date_changed are filled by the database itself.
    protected $useTimestamps = false;

    public function findByEmail(string $email): ?array
    {
        return $this->where('email', strtolower(trim($email)))->first();
    }

    /**
     * Creates the user and gives them the Student role in one transaction.
     * $data['password'] must be the plain password; it is hashed here.
     *
     * @return int|false New user_id, or false on failure
     */
    public function createStudent(array $data)
    {
        $db = $this->db;
        $db->transStart();

        $this->insert([
            'first_name' => trim($data['first_name']),
            'Last_name'  => trim($data['last_name']),
            'email'      => strtolower(trim($data['email'])),
            'password'   => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);
        $userId = (int) $this->getInsertID();

        $db->table('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => self::STUDENT_ROLE_ID,
        ]);

        $db->transComplete();

        return $db->transStatus() ? $userId : false;
    }

    /** @return list<string> Role names, e.g. ['Student'] */
    public function getRoleNames(int $userId): array
    {
        $rows = $this->db->table('user_roles ur')
            ->select('r.role_name')
            ->join('roles r', 'r.role_id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->get()
            ->getResultArray();

        return array_column($rows, 'role_name');
    }
}
