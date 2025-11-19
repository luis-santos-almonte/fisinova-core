<?php
namespace App\Http\Controllers;

use App\Http\Requests\Employee\IndexEmployeeRequest;
use App\Models\Employee;
use App\Services\EmployeeService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    use ApiResponse;

    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function index(IndexEmployeeRequest $request)
    {
        $employees = $this->employeeService->getAllEmployees($request->validated());
        return $this->successResponse($employees);
    }

    public function show(Employee $employee)
    {
        $employee = $this->employeeService->getEmployeeById($employee->id);
        return $this->successResponse($employee);
    }

    public function store(Request $request)
{
    // Validación de entradas
    $validated = $request->validate([
        'firstname'       => 'required|string|max:100',
        'lastname'        => 'required|string|max:100',
        'dni'             => 'required|string|max:20|unique:employees,dni',
        'cellphone'       => 'nullable|string|max:20',
        'phone'           => 'nullable|string|max:20',
        'email'           => 'required|email|unique:employees,email|max:150',
        'address'         => 'nullable|string|max:255',
        'active'          => 'boolean',
        'position_id'     => 'required|exists:positions,id',
        'user_id'         => 'nullable|exists:users,id',
    ]);

    // Crear el empleado usando el servicio
    $employee = $this->employeeService->createEmployee($validated);

    return $this->successResponse(
        $employee,
        201,
        'Empleado creado exitosamente'
    );
}

public function destroy(Employee $employee)
{
    // El servicio se encarga de borrar el registro
    $this->employeeService->deleteEmployee($employee->id);

    return $this->successResponse(
        null,
        200,
        'Empleado eliminado exitosamente'
    );
}

public function update(Request $request, Employee $employee)
{
    // Validación con unique ignorando el empleado actual
    $validated = $request->validate([
        'firstname'       => 'sometimes|required|string|max:100',
        'lastname'        => 'sometimes|required|string|max:100',
        'dni'             => [
            'sometimes', 'required', 'string', 'max:20',
            Rule::unique('employees', 'dni')->ignore($employee->id),
        ],
        'cellphone'       => 'nullable|string|max:20',
        'phone'           => 'nullable|string|max:20',
        'email'           => [
            'sometimes', 'required', 'email', 'max:150',
            Rule::unique('employees', 'email')->ignore($employee->id),
        ],
        'address'         => 'nullable|string|max:255',
        'active'          => 'boolean',
        'position_id'     => 'sometimes|required|exists:positions,id',
        'user_id'         => 'nullable|exists:users,id',
    ]);

    // Actualización usando el servicio
    $updatedEmployee = $this->employeeService->updateEmployee($employee->id, $validated);

    return $this->successResponse(
        $updatedEmployee,
        200,
        'Empleado actualizado exitosamente'
    );
}
}