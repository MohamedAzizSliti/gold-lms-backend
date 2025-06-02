<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\CreateSchoolRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\School;
use App\Models\Store;
use App\Helpers\Helpers;
use App\Repositories\Eloquents\CompanyRepository;
use App\Repositories\Eloquents\SchoolRepository;
use Illuminate\Http\Request;
use App\Http\Requests\CreateStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Repositories\Eloquents\StoreRepository;


class SchoolController extends Controller
{
    public $repository;

    public function __construct(SchoolRepository $repository)
    {


        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $company = $this->filter($this->repository, $request);
        return $company->latest('created_at')->paginate($request->paginate ?? $this->repository->count());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateSchoolRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(School $school)
    {
        return $this->repository->show($school->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCompanyRequest $request, School $school)
    {
        return $this->repository->update($request->all(), $school->getId($request));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, School $school)
    {
        return $this->repository->destroy($school->getId($request));
    }

    /**
     * Update Status the specified resource from storage.
     *
     * @param  int  $id
     * @param int $status
     * @return \Illuminate\Http\Response
     */
    public function status($id, $status)
    {
        return $this->repository->status($id, $status);
    }

    public function deleteAll(Request $request)
    {
        return $this->repository->deleteAll($request->ids);
    }

    public function approve($id, $status)
    {
        return $this->repository->approve($id, $status);
    }

    public function getCompanyBySlug($slug)
    {
        return $this->repository->getCompanyBySlug($slug);
    }


    public function filter($company, $request)
    {
        isset($company->first()->client)?
            $company->first()->client->makeHidden(['company']) : $company;

        if ($request->field && $request->sort) {
            $store = $company->orderBy($request->field, $request->sort);
        }

        if ($request->top_vendor && $request->filter_by) {
         //  $store = Helpers::getTopVendors($store);
        }

        if (isset($request->status)) {
            $company = $company->where('status',$request->status);
        }

        return $company->with(config('enums.company.with'));
    }
}
