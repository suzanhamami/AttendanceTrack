<?php

// Block 5 - Exam and Grades Management | Student name / number: ________

// Block 5 - Exam and Grades Management (student name / number: write them here)

namespace Tests\Unit;

use App\Models\Exam;
use App\Models\Grade;
use PHPUnit\Framework\TestCase;

/**
 * Tests the accessors of the Grade model (percentage, is_passed). No database needed:
 * the models are built in memory.
 */
class GradeAccessorTest extends TestCase
{
    private function makeGrade(string|float|int $grade, string|float|int $max, string|float|int $min): Grade
    {
        $exam = new Exam(['max_grade' => $max, 'min_grade' => $min]);
        $g = new Grade(['grade' => $grade]);
        $g->setRelation('exam', $exam);

        return $g;
    }

    public function test_percentage_is_grade_over_max(): void
    {
        $this->assertSame(75.0, $this->makeGrade(30, 40, 20)->percentage);
        $this->assertSame(33.33, $this->makeGrade('1', '3', 1)->percentage);
        $this->assertSame(0.0, $this->makeGrade(0, 100, 50)->percentage);
    }

    public function test_percentage_is_zero_when_max_is_not_positive(): void
    {
        $this->assertSame(0.0, $this->makeGrade(10, 0, 0)->percentage);
    }

    public function test_grade_passes_when_it_reaches_the_minimum(): void
    {
        $this->assertTrue($this->makeGrade(50, 100, 50)->is_passed);
        $this->assertTrue($this->makeGrade('60.00', 100, 50)->is_passed);
        $this->assertFalse($this->makeGrade(49.99, 100, 50)->is_passed);
    }
}
