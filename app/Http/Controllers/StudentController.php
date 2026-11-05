<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    private $mockStudents = [
    [
        'id' => 1,
        'full_name' => 'Ahmad Ali',
        'university_id' => '20260001',
        'national_id' => '01010112345',
        'major' => 'Informatics Engineering',
        'year' => '2'
    ],
    [
        'id' => 2,
        'full_name' => 'Sara Omar',
        'university_id' => '20260002',
        'national_id' => '02020212345',
        'major' => 'Communications Engineering',
        'year' => '3'
    ],
    [
        'id' => 3,
        'full_name' => 'Khaled Hassan',
        'university_id' => '20260003',
        'national_id' => '03030312345',
        'major' => 'Informatics Engineering',
        'year' => '1'
    ],
    [
        'id' => 4,
        'full_name' => 'Noor Mahmoud',
        'university_id' => '20260004',
        'national_id' => '04040412345',
        'major' => 'Communications Engineering',
        'year' => '4'
    ],
    [
        'id' => 5,
        'full_name' => 'Lina Ahmad',
        'university_id' => '20260005',
        'national_id' => '05050512345',
        'major' => 'Informatics Engineering',
        'year' => '2'
    ],
];
    public function index()
    {
        // عرض قائمة الطلاب
        $students = $this->mockStudents;
        return view('students.index', compact('students'));
    }

    public function create()
    {
        // عرض واجهة إضافة طالب جديد
        return view('students.create');
    }

    public function store(Request $request)
    {
        // تخزين البيانات (محاكاة فقط)
        return redirect()->route('students.index')->with('success', 'تم إضافة الطالب بنجاح');
    }
  
    public function show($id)
    {
        $student = collect($this->mockStudents)->firstWhere('id', $id);
        return view('students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $student = collect($this->mockStudents)->firstWhere('id', $id);
        return view('students.edit', compact('student'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        return redirect()->route('students.index')->with('success', 'Student data updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return redirect()->route('students.index')->with('success', 'Student deleted successfully');
    }

      /**
     * Display a listing of the resource.
     */
//     public function index()
//     {
//         //
//     }

//     /**
//      * Show the form for creating a new resource.
//      */
//     public function create()
//     {
//         //
//     }

//     /**
//      * Store a newly created resource in storage.
//      */
//     public function store(Request $request)
//     {
//         //
//     }

//     /**
//      * Display the specified resource.
//      */
//     public function show(Student $student)
//     {
//         //
//     }

//     /**
//      * Show the form for editing the specified resource.
//      */
//     public function edit(Student $student)
//     {
//         //
//     }

//     /**
//      * Update the specified resource in storage.
//      */
//     public function update(Request $request, Student $student)
//     {
//         //
//     }

//     /**
//      * Remove the specified resource from storage.
//      */
//     public function destroy(Student $student)
//     {
//         //
//     }
}
