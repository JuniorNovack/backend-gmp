<?php

namespace App\Services\Company;


use App\Models\Company;
use App\Services\BaseService;
use Illuminate\Pagination\LengthAwarePaginator;

class CompanyService extends BaseService implements CompanyContract
{

    private function initializeModel(): void
    {
        $this->setModel(new Company());
    }

    /**
     * @inheritDoc
     */
    protected function getModelObject(): Company
    {
        $this->initializeModel();
        return $this->getModel();
    }

    public function getAllCompanies(): LengthAwarePaginator
    {
        return $this->getModelObject()->with('manager')->paginate(10);
    }

    public function createCompany(Company $data): Company
    {
        return $this->insert($data);
    }

    public function updateCompany(Company $data): ?Company
    {
        return  $this->update($data);
    }
    public function deleteCompany(Company $company): void
    {
        $manager = $company->manager;

        $this->delete($company);
        $this->delete($manager);
        $manager->removeRole('manager');
    }

    public function searchCompanies(string $query): array
    {
        return $this->getModelObject()->where('name', 'LIKE', '%' . $query . '%')->get()->toArray();
    }
}
