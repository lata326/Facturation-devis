<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;

class CompanyService
{
    public function createCompany(User $user, array $data): Company
    {
        $data['user_id'] = $user->user_id;
        return Company::create($data);
    }

    public function updateCompany(Company $company, array $data): Company
    {
        $company->update($data);
        return $company->fresh();
    }

    public function deleteCompany(Company $company): bool
    {
        return $company->delete();
    }

    public function getUserCompanies(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $user->companies()->with('formeJuridique')->get();
    }
}