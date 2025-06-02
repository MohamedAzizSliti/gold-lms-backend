<?php

namespace App\Repositories\Eloquents;

use App\Events\CompanyRegisterEvent;
use App\Mail\WelcomeMail;
 use App\Models\School;
 use Exception;
use App\Models\User;

use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use App\Events\VendorRegisterEvent;
use Illuminate\Support\Facades\Hash;
use App\GraphQL\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Illuminate\Support\Facades\Mail;

class SchoolRepository extends BaseRepository
{
    protected $user;
    protected $role;

    protected $fieldSearchable = [
        'company_name' => 'like',
    ];

    public function boot()
    {
        try {
            $this->pushCriteria(app(RequestCriteria::class));
        } catch (ExceptionHandler $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    function model()
    {
        $this->user = new User();
        $this->role = new Role();
        return School::class;
    }

    public function show($id)
    {
        try {

            return $this->model->with(config('enums.company.with'))->findOrFail($id);
        } catch (Exception $e){

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        $clearedPassword = $request->password;
        try {
            $settings = Helpers::getSettings();
            // if ($settings['activation']['multivendor']) {
                $user = $this->user->create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'country_code' => $request->country_code,
                    'phone'    => (string) $request->phone,
                    'password' => Hash::make($request->password),
                    'password_traccar' => $request->password
                ]);

                $user->assignRole(RoleEnum::ADMIN);
                $company = $this->model->create([
                    'school_name' => $request->school_name,
                    'description' => $request->description,
                    'country_id' => $request->country_id,
                    'state_id' => $request->state_id,
                    'city' => $request->city,
                    'address' => $request->address,
                    'pincode' => $request->pincode,
                    'facebook' => $request->facebook,
                    'twitter' => $request->twitter,
                    'instagram'=> $request->instagram,
                    'youtube'=> $request->youtube,
                    'pinterest'=> $request->pinterest,
                    'company_logo_id'=> $request->company_logo_id,
                    'company_cover_id'=> $request->company_cover_id,
                    'hide_client_email' => $request->hide_client_email,
                    'hide_client_phone' => $request->hide_client_phone,
                    'client_id' => $user->id,
                    'status' => $request->status,
                    'is_approved' => $settings['activation']['store_auto_approve'],
                ]);


                DB::commit();


                return $company;


            //throw new Exception('The multi-vendor feature is currently deactivated.', 403);

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $company = $this->model->findOrFail($id);
            $company->update($request);

            if (isset($request['company_logo_id'])) {
                $company->company_logo()->associate($request['company_logo_id']);
            }

            if (isset($request['company_cover_id'])) {
                $company->company_cover()->associate($request['company_cover_id']);
            }

            $company->client->makeHidden(['company']);
            if (isset($request['name'])) {
                $vendor['name'] = $request['name'];
            }

            if (isset($request['email'])) {
                $vendor['email'] = $request['email'];
            }

            if (isset($request['country_code'])) {
                $vendor['country_code'] = $request['country_code'];
            }
            if (isset($request['phone'])) {
                $vendor['phone'] = $request['phone'];
            }
            $company->client->update($vendor);
            DB::commit();
            return $company;

        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {
            $user = $this->model->findOrFail($id);

            return $user->destroy($id);

        } catch (Exception $e){

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function status($id, $status)
    {
        try {

            $company = $this->model->findOrFail($id);
            $company->update(['status' => $status]);

            return $company;

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function deleteAll($ids)
    {
        try {

            $companys = $this->model->whereIn('id', $ids)->get();
            foreach($companys as $company) {
                $this->model->findOrFail($company->id)->destroy($company->id);
            }

            return true;

        } catch (Exception $e){

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function approve($id, $approve)
    {
        try {

            $company = $this->model->findOrFail($id);
            $company->update(['is_approved' => $approve]);

            $company = $company->fresh();
            $company->total_in_approved_companys = $this->model->where('is_approved', false)->count();

            return $company;

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

        public function getCompanyBySlug($slug)
        {
            try {

                return $this->model->where('slug', $slug)->firstOrFail();

            } catch (Exception $e) {

                throw new ExceptionHandler($e->getMessage(), $e->getCode());
            }
        }
}
