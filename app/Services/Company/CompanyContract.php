<?php

namespace App\Services\Company;

use App\Models\Company;
use App\Services\Contracts\IBaseService;
use Illuminate\Pagination\LengthAwarePaginator;

interface CompanyContract extends IBaseService
{
    public function getAllCompanies(): LengthAwarePaginator;

    public function createCompany(Company $data): Company;

    public function updateCompany(Company $data): ?Company;

    public function deleteCompany(Company $company): void;

    public function searchCompanies(string $query): array;
}
