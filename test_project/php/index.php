<?php
// 1. Клас Student
class Student {
    public $id;
    public $name;
    public $age;
    public $grade;
    
    public function __construct($id, $name, $age, $grade) {
        $this->id = $id;
        $this->name = $name;
        $this->age = $age;
        $this->grade = $grade;
    }
    
    public function displayInfo() {
        echo "ID: {$this->id} | Ім'я: {$this->name} | Вік: {$this->age} | Оцінка: {$this->grade}\n";
    }
    
    public function isExcellent() {
        return $this->grade >= 90;
    }
}

class StudentManager {
    private $students = [];
    
    public function addStudent(Student $student) {
        $this->students[] = $student;
        echo "✅ Студента '{$student->name}' додано успішно!\n";
    }
    
    public function showAllStudents() {
        echo "\n=== СПИСОК УСІХ СТУДЕНТІВ ===\n";
        if (empty($this->students)) {
            echo "Список порожній.\n";
            return;
        }
        
        foreach ($this->students as $student) {
            $student->displayInfo();
        }
    }
    
    public function findStudentByName($name) {
        echo "\n🔍 Пошук студента: '$name'\n";
        $found = false;
        
        foreach ($this->students as $student) {
            if (strpos(strtolower($student->name), strtolower($name)) !== false) {
                $student->displayInfo();
                $found = true;
            }
        }
        
        if (!$found) {
            echo "Студента не знайдено.\n";
        }
    }
    
    public function getAverageGrade() {
        if (empty($this->students)) return 0;
        
        $total = 0;
        foreach ($this->students as $student) {
            $total += $student->grade;
        }
        
        return $total / count($this->students);
    }
}

echo "🎓 СИСТЕМА КЕРУВАННЯ СТУДЕНТАМИ\n";
echo "================================\n";

$manager = new StudentManager();

$student1 = new Student(1, "Іван Петренко", 20, 85);
$student2 = new Student(2, "Марія Іваненко", 21, 92);
$student3 = new Student(3, "Олександр Коваленко", 19, 78);
$student4 = new Student(4, "Анна Сидоренко", 22, 95);

$manager->addStudent($student1);
$manager->addStudent($student2);
$manager->addStudent($student3);
$manager->addStudent($student4);

$manager->showAllStudents();

$manager->findStudentByName("Марія");

$average = $manager->getAverageGrade();
echo "\n📊 Середня оцінка групи: " . round($average, 2) . "\n";

echo "\n=== ДОДАТКОВА ІНФОРМАЦІЯ ===\n";

echo "\n🌟 Відмінники (оцінка ≥ 90):\n";
foreach ($manager as $student) {
    if ($student->isExcellent()) {
        echo "- {$student->name} ({$student->grade} балів)\n";
    }
}

echo "\n\n📝 ДОДАВАННЯ НОВОГО СТУДЕНТА\n";
echo "==============================\n";

$student5 = new Student(5, "Богдан Шевченко", 23, 88);
$manager->addStudent($student5);

echo "\n=== ОНОВЛЕНИЙ СПИСОК ===\n";
$manager->showAllStudents();

echo "\n📈 СТАТИСТИКА:\n";
echo "Кількість студентів: " . count($manager) . "\n";
echo "Середня оцінка: " . round($manager->getAverageGrade(), 2) . "\n";

echo "\n\n✅ Програму завершено успішно!\n";
echo "Час виконання: " . date('H:i:s') . "\n";

?>
