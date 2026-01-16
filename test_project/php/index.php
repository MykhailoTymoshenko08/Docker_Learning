<?php
// ============================================================================
// КОМПЛЕКСНА СИСТЕМА УПРАВЛІННЯ УНІВЕРСИТЕТОМ НА PHP (400+ СТРОК)
// ============================================================================

declare(strict_types=1);

// 1. БАЗОВІ КЛАСИ
// ============================================================================

abstract class Person {
    protected int $id;
    protected string $name;
    protected string $email;
    protected DateTime $birthDate;
    
    public function __construct(int $id, string $name, string $email, string $birthDate) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->birthDate = new DateTime($birthDate);
    }
    
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getAge(): int {
        $now = new DateTime();
        $interval = $this->birthDate->diff($now);
        return $interval->y;
    }
    
    abstract public function getRole(): string;
    abstract public function getDetails(): array;
    
    public function displayInfo(): string {
        return sprintf("%s (ID: %d, Email: %s, Вік: %d)", 
            $this->name, $this->id, $this->email, $this->getAge());
    }
}

// 2. КЛАС СТУДЕНТА
// ============================================================================

class Student extends Person {
    private string $studentId;
    private string $faculty;
    private int $year;
    private array $grades = [];
    private float $scholarship = 0;
    
    public function __construct(int $id, string $name, string $email, string $birthDate, 
                                string $studentId, string $faculty, int $year) {
        parent::__construct($id, $name, $email, $birthDate);
        $this->studentId = $studentId;
        $this->faculty = $faculty;
        $this->year = $year;
    }
    
    public function getRole(): string { return 'Студент'; }
    
    public function addGrade(string $subject, int $grade, string $date): void {
        $this->grades[] = [
            'subject' => $subject,
            'grade' => $grade,
            'date' => $date,
            'teacher' => null
        ];
    }
    
    public function addGradeWithTeacher(string $subject, int $grade, string $date, Teacher $teacher): void {
        $this->grades[] = [
            'subject' => $subject,
            'grade' => $grade,
            'date' => $date,
            'teacher' => $teacher->getName()
        ];
    }
    
    public function getAverageGrade(): float {
        if (empty($this->grades)) return 0;
        
        $sum = 0;
        foreach ($this->grades as $grade) {
            $sum += $grade['grade'];
        }
        return round($sum / count($this->grades), 2);
    }
    
    public function getGradesBySubject(string $subject): array {
        return array_filter($this->grades, fn($g) => $g['subject'] === $subject);
    }
    
    public function setScholarship(float $amount): void {
        $this->scholarship = $amount;
    }
    
    public function getScholarship(): float {
        // Автоматичне нарахування за успішність
        $average = $this->getAverageGrade();
        if ($average >= 90) {
            return $this->scholarship * 1.5;
        } elseif ($average >= 75) {
            return $this->scholarship;
        }
        return 0;
    }
    
    public function getDetails(): array {
        return [
            'student_id' => $this->studentId,
            'faculty' => $this->faculty,
            'year' => $this->year,
            'average_grade' => $this->getAverageGrade(),
            'grades_count' => count($this->grades),
            'scholarship' => $this->getScholarship()
        ];
    }
    
    public function promoteToNextYear(): void {
        if ($this->year < 5 && $this->getAverageGrade() >= 60) {
            $this->year++;
            echo "🎓 Студента {$this->name} переведено на {$this->year} курс!\n";
        }
    }
    
    public function displayFullInfo(): string {
        $info = parent::displayInfo();
        $details = $this->getDetails();
        return $info . sprintf(", Факультет: %s, Курс: %d, Середній бал: %.2f", 
            $details['faculty'], $details['year'], $details['average_grade']);
    }
}

// 3. КЛАС ВИКЛАДАЧА
// ============================================================================

class Teacher extends Person {
    private string $department;
    private string $academicDegree;
    private float $salary;
    private array $subjects = [];
    private array $assignedStudents = [];
    
    public function __construct(int $id, string $name, string $email, string $birthDate,
                                string $department, string $academicDegree, float $salary) {
        parent::__construct($id, $name, $email, $birthDate);
        $this->department = $department;
        $this->academicDegree = $academicDegree;
        $this->salary = $salary;
    }
    
    public function getRole(): string { return 'Викладач'; }
    
    public function addSubject(string $subject): void {
        if (!in_array($subject, $this->subjects)) {
            $this->subjects[] = $subject;
        }
    }
    
    public function assignStudent(Student $student): void {
        $this->assignedStudents[$student->getId()] = $student;
    }
    
    public function gradeStudent(Student $student, string $subject, int $grade): void {
        $student->addGradeWithTeacher($subject, $grade, date('Y-m-d'), $this);
        echo "✅ {$this->name} виставив оцінку {$grade} студенту {$student->getName()} з предмету {$subject}\n";
    }
    
    public function getStudentsAverageGrade(): float {
        if (empty($this->assignedStudents)) return 0;
        
        $sum = 0;
        $count = 0;
        foreach ($this->assignedStudents as $student) {
            $sum += $student->getAverageGrade();
            $count++;
        }
        return round($sum / $count, 2);
    }
    
    public function getDetails(): array {
        return [
            'department' => $this->department,
            'academic_degree' => $this->academicDegree,
            'salary' => $this->salary,
            'subjects' => $this->subjects,
            'students_count' => count($this->assignedStudents),
            'average_students_grade' => $this->getStudentsAverageGrade()
        ];
    }
    
    public function calculateSalaryWithBonus(): float {
        $baseSalary = $this->salary;
        $studentsAvg = $this->getStudentsAverageGrade();
        
        // Бонус за успішність студентів
        if ($studentsAvg >= 85) {
            $baseSalary *= 1.2;
        } elseif ($studentsAvg >= 70) {
            $baseSalary *= 1.1;
        }
        
        // Бонус за стаж
        $experience = $this->getAge() - 22; // Припустимий вік початку роботи
        if ($experience > 10) {
            $baseSalary *= 1.15;
        } elseif ($experience > 5) {
            $baseSalary *= 1.05;
        }
        
        return round($baseSalary, 2);
    }
}

// 4. КЛАС КУРСУ
// ============================================================================

class Course {
    private string $code;
    private string $name;
    private Teacher $teacher;
    private int $credits;
    private array $students = [];
    private array $schedule = [];
    
    public function __construct(string $code, string $name, Teacher $teacher, int $credits) {
        $this->code = $code;
        $this->name = $name;
        $this->teacher = $teacher;
        $this->credits = $credits;
        $teacher->addSubject($name);
    }
    
    public function enrollStudent(Student $student): void {
        if (!isset($this->students[$student->getId()])) {
            $this->students[$student->getId()] = $student;
            $this->teacher->assignStudent($student);
            echo "✅ Студент {$student->getName()} записався на курс {$this->name}\n";
        }
    }
    
    public function addSchedule(string $day, string $time, string $room): void {
        $this->schedule[] = [
            'day' => $day,
            'time' => $time,
            'room' => $room
        ];
    }
    
    public function conductExam(): array {
        $results = [];
        echo "\n📝 ПРОВЕДЕННЯ ІСПИТУ З КУРСУ: {$this->name}\n";
        echo "=========================================\n";
        
        foreach ($this->students as $student) {
            // Симуляція іспиту
            $grade = rand(60, 100);
            $this->teacher->gradeStudent($student, $this->name, $grade);
            
            $results[$student->getId()] = [
                'student' => $student->getName(),
                'grade' => $grade,
                'passed' => $grade >= 60
            ];
        }
        
        return $results;
    }
    
    public function getCourseInfo(): array {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'teacher' => $this->teacher->getName(),
            'credits' => $this->credits,
            'students_count' => count($this->students),
            'schedule' => $this->schedule
        ];
    }
}

