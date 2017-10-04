<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\EmployeeRepository;
use App\Repositories\Interfaces\RolesRepository;
use Gate;

class EmployeeController extends Controller
{
    private $empRepo;
    private $empRoleRepo;

    public function __construct(EmployeeRepository $emp, RolesRepository $role)
    {
        $this->empRepo = $emp;
        $this->empRoleRepo = $role;
    }

    public function index(Request $request)
    {
        $emps = $this->empRepo->all();
        $roles = $this->empRoleRepo->all();
    	return view('pages.emp.index', compact(['emps', 'roles']));
    }

    public function profile(Request $request, $id)
    {
        $emp = $this->empRepo->find($id);
        return view('pages.emp.profile', compact(['emp']));
    }

    public function store(Request $request)
    {
        $input = $request->only('name', 'phone', 'password', 'birthday', 'start_date', 'sex');
        $input['role']  = "3";
        $input['password']  = bcrypt($input['password']);
        $this->empRepo->create($input);

        return redirect()->route('employees.index')->with('message', 'Thêm thành công.');
    }

    public function update(Request $request, $id)
    {
        // Check deny update user
        if (Gate::denies('update',  [auth()->user(), $id]))
            return redirect()->route('employees.index');

        if (!auth()->user()->hasRole('admin'))
            $input = $request->only('name', 'sex', 'old_pwd', 'password', 'birthday', 'start_date', 'address');
        else
            $input = $request->only('name', 'phone', 'sex', 'old_pwd', 'password', 'birthday', 'start_date', 'address', 'salary', 'fileID');

        if (isset($input['old_pwd']) && isset($input['password']))
        {
            $input['password'] = bcrypt($input['password']);
            if (Hash::check($input['old_pwd'], $this->empRepo->find($id)->password)){
                $this->empRepo->update($input, $id);
                return redirect()->route('employees.index')->with('message', 'Cập nhật thành công');
            }
            return redirect()->route('employees.profile', $id)->with('message', 'Mật khẩu cũ không đúng');
        }

        $input = array_filter($input, function($var){return !is_null($var);});
        if ($this->empRepo->update($input, $id))
        {
            return redirect()->route('employees.index')->with('message', 'Cập nhật thành công');
        }
        unset($input);
        return redirect()->route('employees.profile', $id)->with('message', 'Không sửa được thông tin.');

    }

    public function destroy(Request $request)
    {
        try
        {
            $this->empRepo->deleteMultiRecord($request->emps);
        }
        catch (Exception $e)
        {
            dd($e);
        }
        return redirect()->route('employees.index')->with('message', 'Successfully deleted.');
    }
}
