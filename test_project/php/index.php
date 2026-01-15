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

// 5. КЛАС ФАКУЛЬТЕТУ
// ============================================================================

class Faculty {
    private string $name;
    private string $dean;
    private array $departments = [];
    private array $courses = [];
    private array $statistics = [
        'total_students' => 0,
        'total_teachers' => 0,
        'average_grade' => 0,
        'graduated' => 0
    ];
    
    public function __construct(string $name, string $dean) {
        $this->name = $name;
        $this->dean = $dean;
    }
    
    public function addDepartment(string $department): void {
        $this->departments[] = $department;
    }
    
    public function addCourse(Course $course): void {
        $this->courses[$course->getCourseInfo()['code']] = $course;
    }
    
    public function registerStudent(Student $student): void {
        $this->statistics['total_students']++;
    }
    
    public function registerTeacher(Teacher $teacher): void {
        $this->statistics['total_teachers']++;
    }
    
    public function updateStatistics(): void {
        // Оновлення статистики факультету
        $totalGrades = 0;
        $gradeCount = 0;
        
        // Тут була б логіка збору статистики з курсів
        $this->statistics['average_grade'] = $gradeCount > 0 ? 
            round($totalGrades / $gradeCount, 2) : 0;
    }
    
    public function getFacultyReport(): array {
        $this->updateStatistics();
        return [
            'faculty_name' => $this->name,
            'dean' => $this->dean,
            'departments_count' => count($this->departments),
            'courses_count' => count($this->courses),
            'statistics' => $this->statistics
        ];
    }
}

// 6. КЛАС УНІВЕРСИТЕТУ (Головний менеджер)
// ============================================================================

class University {
    private string $name;
    private string $address;
    private array $faculties = [];
    private array $allStudents = [];
    private array $allTeachers = [];
    private array $allCourses = [];
    private Database $database;
    
    public function __construct(string $name, string $address) {
        $this->name = $name;
        $this->address = $address;
        $this->database = new Database();
    }
    
    public function addFaculty(Faculty $faculty): void {
        $this->faculties[$faculty->getFacultyReport()['faculty_name']] = $faculty;
    }
    
    public function registerPerson(Person $person): void {
        if ($person instanceof Student) {
            $this->allStudents[$person->getId()] = $person;
            $this->database->saveStudent($person);
        } elseif ($person instanceof Teacher) {
            $this->allTeachers[$person->getId()] = $person;
            $this->database->saveTeacher($person);
        }
    }
    
    public function createCourse(string $code, string $name, Teacher $teacher, int $credits): Course {
        $course = new Course($code, $name, $teacher, $credits);
        $this->allCourses[$code] = $course;
        $this->database->saveCourse($course);
        return $course;
    }
    
    public function findStudentById(int $id): ?Student {
        return $this->allStudents[$id] ?? null;
    }
    
    public function findTeacherById(int $id): ?Teacher {
        return $this->allTeachers[$id] ?? null;
    }
    
    public function getUniversityStatistics(): array {
        $totalStudents = count($this->allStudents);
        $totalTeachers = count($this->allTeachers);
        $totalCourses = count($this->allCourses);
        
        // Розрахунок середніх оцінок
        $totalAvgGrade = 0;
        foreach ($this->allStudents as $student) {
            $totalAvgGrade += $student->getAverageGrade();
        }
        $avgGrade = $totalStudents > 0 ? round($totalAvgGrade / $totalStudents, 2) : 0;
        
        // Розрахунок середньої зарплати
        $totalSalary = 0;
        foreach ($this->allTeachers as $teacher) {
            $totalSalary += $teacher->calculateSalaryWithBonus();
        }
        $avgSalary = $totalTeachers > 0 ? round($totalSalary / $totalTeachers, 2) : 0;
        
        return [
            'university_name' => $this->name,
            'address' => $this->address,
            'total_faculties' => count($this->faculties),
            'total_students' => $totalStudents,
            'total_teachers' => $totalTeachers,
            'total_courses' => $totalCourses,
            'average_grade' => $avgGrade,
            'average_salary' => $avgSalary,
            'student_to_teacher_ratio' => $totalTeachers > 0 ? 
                round($totalStudents / $totalTeachers, 2) : 0
        ];
    }
    
    public function generateReport(): string {
        $stats = $this->getUniversityStatistics();
        $report = "\n" . str_repeat("=", 60) . "\n";
        $report .= "ЗВІТ УНІВЕРСИТЕТУ: {$stats['university_name']}\n";
        $report .= str_repeat("=", 60) . "\n";
        $report .= "Адреса: {$stats['address']}\n";
        $report .= "Факультетів: {$stats['total_faculties']}\n";
        $report .= "Студентів: {$stats['total_students']}\n";
        $report .= "Викладачів: {$stats['total_teachers']}\n";
        $report .= "Курсів: {$stats['total_courses']}\n";
        $report .= "Середній бал: {$stats['average_grade']}\n";
        $report .= "Середня зарплата викладача: \${$stats['average_salary']}\n";
        $report .= "Співвідношення студент/викладач: {$stats['student_to_teacher_ratio']}\n";
        $report .= str_repeat("=", 60) . "\n";
        
        return $report;
    }
    
    public function simulateAcademicYear(): void {
        echo "\n🎬 СИМУЛЯЦІЯ НАВЧАЛЬНОГО РОКУ\n";
        echo str_repeat("=", 50) . "\n";
        
        // Студенти складають іспити
        foreach ($this->allCourses as $course) {
            $course->conductExam();
        }
        
        // Підвищення курсів
        foreach ($this->allStudents as $student) {
            $student->promoteToNextYear();
        }
        
        // Розрахунок стипендій
        $totalScholarships = 0;
        foreach ($this->allStudents as $student) {
            $scholarship = rand(1000, 3000);
            $student->setScholarship($scholarship);
            $totalScholarships += $student->getScholarship();
        }
        
        echo "\n💰 ЗАГАЛЬНА СУМА СТИПЕНДІЙ: \${$totalScholarships}\n";
    }
}

// 7. КЛАС БАЗИ ДАНИХ (СИМУЛЯЦІЯ)
// ============================================================================

class Database {
    private array $data = [
        'students' => [],
        'teachers' => [],
        'courses' => [],
        'logs' => []
    ];
    
    public function saveStudent(Student $student): void {
        $this->data['students'][$student->getId()] = [
            'id' => $student->getId(),
            'name' => $student->getName(),
            'details' => $student->getDetails(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        $this->log("Збережено студента: {$student->getName()}");
    }
    
    public function saveTeacher(Teacher $teacher): void {
        $this->data['teachers'][$teacher->getId()] = [
            'id' => $teacher->getId(),
            'name' => $teacher->getName(),
            'details' => $teacher->getDetails(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        $this->log("Збережено викладача: {$teacher->getName()}");
    }
    
    public function saveCourse(Course $course): void {
        $info = $course->getCourseInfo();
        $this->data['courses'][$info['code']] = $info;
        $this->log("Створено курс: {$info['name']}");
    }
    
    private function log(string $message): void {
        $this->data['logs'][] = [
            'message' => $message,
            'timestamp' => microtime(true)
        ];
    }
    
    public function getStatistics(): array {
        return [
            'total_students' => count($this->data['students']),
            'total_teachers' => count($this->data['teachers']),
            'total_courses' => count($this->data['courses']),
            'total_logs' => count($this->data['logs'])
        ];
    }
}

// 8. ІНТЕРФЕЙС КОРИСТУВАЧА (СИМУЛЯЦІЯ)
// ============================================================================

class UniversityUI {
    private University $university;
    
    public function __construct(University $university) {
        $this->university = $university;
    }
    
    public function displayMainMenu(): void {
        echo "\n" . str_repeat("═", 50) . "\n";
        echo "🏛️  СИСТЕМА УПРАВЛІННЯ УНІВЕРСИТЕТОМ\n";
        echo str_repeat("═", 50) . "\n";
        echo "1. 📊 Переглянути статистику\n";
        echo "2. 👨‍🎓 Додати студента\n";
        echo "3. 👨‍🏫 Додати викладача\n";
        echo "4. 📚 Створити курс\n";
        echo "5. 🎬 Симулювати навчальний рік\n";
        echo "6. 📄 Згенерувати звіт\n";
        echo "7. 🚪 Вихід\n";
        echo str_repeat("═", 50) . "\n";
    }
    
    public function handleChoice(int $choice): bool {
        switch ($choice) {
            case 1:
                $this->showStatistics();
                break;
            case 2:
                $this->addSampleStudent();
                break;
            case 3:
                $this->addSampleTeacher();
                break;
            case 4:
                $this->addSampleCourse();
                break;
            case 5:
                $this->university->simulateAcademicYear();
                break;
            case 6:
                echo $this->university->generateReport();
                break;
            case 7:
                echo "👋 До побачення!\n";
                return false;
            default:
                echo "❌ Невірний вибір!\n";
        }
        return true;
    }
    
    private function showStatistics(): void {
        $stats = $this->university->getUniversityStatistics();
        echo "\n📈 СТАТИСТИКА УНІВЕРСИТЕТУ:\n";
        foreach ($stats as $key => $value) {
            echo "  " . ucfirst(str_replace('_', ' ', $key)) . ": {$value}\n";
        }
    }
    
    private function addSampleStudent(): void {
        static $studentId = 1000;
        $student = new Student(
            $studentId++,
            "Студент_" . $studentId,
            "student{$studentId}@edu.ua",
            "2000-06-15",
            "S" . str_pad($studentId, 6, '0', STR_PAD_LEFT),
            "Комп'ютерні науки",
            rand(1, 4)
        );
        
        // Додаємо випадкові оцінки
        $subjects = ['Математика', 'Програмування', 'Бази даних', 'Алгоритми'];
        foreach ($subjects as $subject) {
            $student->addGrade($subject, rand(60, 100), date('Y-m-d'));
        }
        
        $this->university->registerPerson($student);
        echo "✅ Додано нового студента: {$student->getName()}\n";
    }
    
    private function addSampleTeacher(): void {
        static $teacherId = 500;
        $teacher = new Teacher(
            $teacherId++,
            "Викладач_" . $teacherId,
            "teacher{$teacherId}@edu.ua",
            "197" . rand(0, 9) . "-" . sprintf("%02d", rand(1, 12)) . "-" . sprintf("%02d", rand(1, 28)),
            "Кафедра інформатики",
            ["Кандидат наук", "Доктор наук"][rand(0, 1)],
            rand(15000, 35000)
        );
        
        $this->university->registerPerson($teacher);
        echo "✅ Додано нового викладача: {$teacher->getName()}\n";
    }
    
    private function addSampleCourse(): void {
        static $courseNum = 1;
        $teachers = $this->university->getUniversityStatistics()['total_teachers'];
        
        if ($teachers > 0) {
            $course = $this->university->createCourse(
                "CS" . str_pad($courseNum, 3, '0', STR_PAD_LEFT),
                "Курс_" . $courseNum,
                $this->university->findTeacherById(500), // перший викладач
                rand(3, 6)
            );
            echo "✅ Створено новий курс: {$course->getCourseInfo()['name']}\n";
            $courseNum++;
        } else {
            echo "❌ Спочатку додайте викладача!\n";
        }
    }
}

// 9. ОСНОВНА ПРОГРАМА
// ============================================================================

function main(): void {
    echo "🚀 ЗАПУСК СИСТЕМИ УПРАВЛІННЯ УНІВЕРСИТЕТОМ\n";
    
    // Створення університету
    $university = new University("Національний технічний університет", "Київ, пр. Перемоги, 37");
    
    // Створення факультету
    $faculty = new Faculty("Факультет інформатики", "Проф. Іваненко І.І.");
    $faculty->addDepartment("Кафедра програмної інженерії");
    $faculty->addDepartment("Кафедра штучного інтелекту");
    $university->addFaculty($faculty);
    
    // Додаємо прикладних викладачів та студентів
    $teacher1 = new Teacher(1, "Проф. Петренко П.П.", "petrenko@ntu.ua", "1975-03-15", 
                           "Кафедра програмної інженерії", "Доктор наук", 28000);
    $teacher2 = new Teacher(2, "Доц. Сидоренко С.С.", "sidorenko@ntu.ua", "1985-08-22",
                           "Кафедра штучного інтелекту", "Кандидат наук", 22000);
    
    $student1 = new Student(101, "Іваненко Іван", "ivanenko@student.ntu.ua", "2003-05-20",
                           "S2023101", "Комп'ютерні науки", 2);
    $student2 = new Student(102, "Петрова Марія", "petrova@student.ntu.ua", "2002-11-10",
                           "S2023102", "Програмна інженерія", 3);
    
    // Додаємо оцінки студентам
    $student1->addGrade("Програмування", 95, "2024-01-15");
    $student1->addGrade("Математика", 88, "2024-01-20");
    $student2->addGrade("Програмування", 78, "2024-01-15");
    $student2->addGrade("Бази даних", 92, "2024-01-18");
    
    // Реєстрація в університеті
    $university->registerPerson($teacher1);
    $university->registerPerson($teacher2);
    $university->registerPerson($student1);
    $university->registerPerson($student2);
    
    // Створення курсів
    $course1 = $university->createCourse("CS101", "Основи програмування", $teacher1, 5);
    $course2 = $university->createCourse("CS201", "Бази даних", $teacher2, 4);
    
    // Запис студентів на курси
    $course1->enrollStudent($student1);
    $course1->enrollStudent($student2);
    $course2->enrollStudent($student1);
    
    // Додаємо розклад
    $course1->addSchedule("Понеділок", "10:00-11:30", "Ауд. 301");
    $course1->addSchedule("Середа", "14:00-15:30", "Ауд. 215");
    
    // Створення інтерфейсу
    $ui = new UniversityUI($university);
    
    // Симуляція меню (для прикладу - 3 ітерації)
    for ($i = 0; $i < 3; $i++) {
        $ui->displayMainMenu();
        $choice = rand(1, 7); // Імітація вибору
        echo "Обрано: {$choice}\n";
        $ui->handleChoice($choice);
    }
    
    // Фінальний звіт
    echo $university->generateReport();
    
    // Демонстрація роботи
    echo "\n👨‍🎓 ПРИКЛАД СТУДЕНТА:\n";
    echo $student1->displayFullInfo() . "\n";
    
    echo "\n👨‍🏫 ПРИКЛАД ВИКЛАДАЧА:\n";
    echo "{$teacher1->getName()}, зарплата з бонусами: \${$teacher1->calculateSalaryWithBonus()}\n";
    
    echo "\n📚 ПРИКЛАД КУРСУ:\n";
    $info = $course1->getCourseInfo();
    echo "{$info['name']} (код: {$info['code']}), викладач: {$info['teacher']}, кредитів: {$info['credits']}\n";
}

// 10. ЗАПУСК ПРОГРАМИ
// ============================================================================

// Перевірка версії PHP
if (version_compare(PHP_VERSION, '7.4.0') < 0) {
    die("❌ Потрібна PHP версія 7.4 або вище!\n");
}

// Запуск головної функції
try {
    main();
} catch (Exception $e) {
    echo "❌ Помилка: " . $e->getMessage() . "\n";
    echo "Файл: " . $e->getFile() . ", рядок: " . $e->getLine() . "\n";
}

// Фінальне повідомлення
echo "\n" . str_repeat("⭐", 25) . "\n";
echo "ПРОГРАМА УСПІШНО ЗАВЕРШЕНА!\n";
echo str_repeat("⭐", 25) . "\n";

// Додаткова інформація
echo "\nℹ️  Інформація про систему:\n";
echo "Час виконання: " . date('Y-m-d H:i:s') . "\n";
echo "Використано пам'яті: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB\n";
echo "Час виконання скрипта: " . round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3) . " сек\n";

?>
