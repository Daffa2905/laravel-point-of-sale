<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\AdvanceSalary;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;

class AdvanceSalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $row = (int) request('row', 10);

        if ($row < 1 || $row > 100) {
            abort(400, 'The per_page parameter must be an integer between 1 and 100.');
        }

        if(request('search')){
            Employee::firstWhere('name', request('search'));
        }

        return view('advance-salary.index', [
            'user' => auth()->user(),
            'advance_salaries' => AdvanceSalary::with(['employee'])
                ->filter(request(['search']))
                ->sortable()
                ->paginate($row)
                ->appends(request()->query()),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('advance-salary.create', [
            'user' => auth()->user(),
            'employees' => Employee::all()->sortBy('name'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'employee_id' => 'required',
            'date' => 'required|date_format:Y-m-d|max:10',
            'advance_salary' => 'required|numeric'
        ];

        // format date only shows the year and month
        $getYm = Carbon::createFromFormat('Y-m-d', $request->date)->format('Y-m');

        $advanced = AdvanceSalary::where('employee_id', $request->employee_id)
            ->whereDate('date', 'LIKE',  $getYm . '%')
            ->get();

        if ($advanced->isEmpty()) {
            $validatedData = $request->validate($rules);
            AdvanceSalary::create($validatedData);

            return Redirect::route('advance-salary.create')->with('success', 'Advance Salary Paid Successfully!');
        } else {
            return Redirect::route('advance-salary.create')->with('warning', 'Advance Salary Already Paid!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AdvanceSalary $advanceSalary)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdvanceSalary $advanceSalary)
    {
        return view('advance-salary.edit', [
            'user' => auth()->user(),
            'employees' => Employee::all()->sortBy('name'),
            'advance_salary' => $advanceSalary,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AdvanceSalary $advanceSalary)
    {
        $rules = [
            'employee_id' => 'required',
            'date' => 'required|date_format:Y-m-d|max:10',
            'advance_salary' => 'required|numeric'
        ];

        // format date only shows the year and month
        $getYm = Carbon::createFromFormat('Y-m-d', $request->date)->format('Y-m');

        $advanced = AdvanceSalary::where('employee_id', $request->employee_id)
            ->whereDate('date', 'LIKE',  $getYm . '%')
            ->get();

        if ($advanced->isEmpty()) {
            $validatedData = $request->validate($rules);
            AdvanceSalary::where('id', $advanceSalary->id)->update($validatedData);

            return Redirect::route('advance-salary.edit', $request->employee_id)->with('success', 'Advance Salary Updated Successfully!');
        } else {
            return Redirect::route('advance-salary.edit', $request->employee_id)->with('warning', 'Advance Salary Already Paid!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdvanceSalary $advanceSalary)
    {
        AdvanceSalary::destroy($advanceSalary->id);

        return Redirect::route('advance-salary.index')->with('success', 'Advance Salary has been deleted!');
    }
}