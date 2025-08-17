<?php

namespace App\Services;

use App\Models\Admin;
use App\Repositories\AdminRepository;

class AdminService extends BaseService
{
    protected $adminRepository;

    public function __construct(AdminRepository $adminRepository)
    {
        parent::__construct($adminRepository);
        $this->adminRepository = $adminRepository;
    }

    public function storeOrUpdateAdmin($data)
    {
        if (isset($data['id'])) {
            return $this->update($data['id'], $data);
        } else {
            return $this->create($data);
        }
    }
}
