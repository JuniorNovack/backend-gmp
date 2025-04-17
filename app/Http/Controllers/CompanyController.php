<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Exceptions\Handler;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Services\Contracts\IUserService;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\CompanyFormRequest;
use App\Services\Company\CompanyContract;
use App\Http\Requests\UpdateCompanyRequest;
use Illuminate\Http\Response as HttpRequest;

class CompanyController extends Controller
{
    protected $companyService;
    protected $userService;

    public function __construct(CompanyContract $companyService, IUserService $userService)
    {
        $this->companyService = $companyService;
        $this->userService = $userService;

        $this->middleware('permission:create_companies', ['only' => ['store']]);
        $this->middleware('permission:edit_companies', ['only' => ['update']]);
        $this->middleware('permission:delete_companies', ['only' => ['destroy']]);
        $this->middleware('permission:view_companies', ['only' => ['index', 'show']]);
    }

    public function index()
    {
        return Response::success($this->companyService->getAllCompanies() ?? []);
    }

    public function show($id)
    {
        $response = $this->companyService->findModelById($id);
        return Response::success($response);
    }

    public function store(CompanyFormRequest $request)
    {
        try {
            $this->userService->getUserByEmail($request->manager_email);

            $manager = new User([
                'name' => $request->manager_name,
                'email' => $request->manager_email,
                'password' => Hash::make($request->manager_password),
                'phone' => $request->phone,
            ]);
            $createdUserResponse = $this->userService->createAccount($manager);
            $createdUserResponse->assignRole('manager');

            $company = new Company([
                'name' => $request->name,
                'address' => $request->address,
                'phone' => $request->phone,
                'manager_id' => $manager->id,
                'screens_allowed' => $request->screens_allowed,
            ]);

            $createdCompnyResponse = $this->companyService->createCompany($company);
            $manager = $createdCompnyResponse->manager;

            if ($manager) {
                $manager->assignRole('manager');
            }

            return Response::success([
                'message' => 'Entreprise créée avec succès',
                'company' => $createdCompnyResponse->load('manager'),
            ], HttpRequest::HTTP_CREATED);
        } catch (\Exception $e) {
            app()->get(Handler::class)->report($e);
            return Response::error($e->getMessage());
        }
    }

    public function update(UpdateCompanyRequest $request, $id)
    {
        $company = $this->companyService->findModelById($id);

        $company->forceFill(array_filter([
            'name' => $request->get('name'),
            'address' => $request->get('address'),
            'phone' => $request->get('phone'),
            'screens_allowed' => $request->get('screens_allowed'),
            'active' => $request->get('active'),
        ]));

        $companyUpdatedResponse = $this->companyService->updateCompany($company);

        return Response::success([
            'message' => 'Entreprise mise à jour avec succès',
            'company' => $companyUpdatedResponse
        ]);
    }

    public function destroy($id)
    {
        $company = $this->companyService->findModelById($id);

        $this->companyService->deleteCompany($company);

        return Response::success(['message' => 'Entreprise supprimé avec succès']);
    }
}
