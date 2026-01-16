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



