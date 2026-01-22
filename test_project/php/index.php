// <?php
// // ============================================================================
// // КОМПЛЕКСНА СИСТЕМА УПРАВЛІННЯ УНІВЕРСИТЕТОМ НА PHP (400+ СТРОК)
// // ============================================================================

// declare(strict_types=1);

// // 1. БАЗОВІ КЛАСИ
// // ============================================================================

// abstract class Person {
//     protected int $id;
//     protected string $name;
//     protected string $email;
//     protected DateTime $birthDate;
    
//     public function __construct(int $id, string $name, string $email, string $birthDate) {
//         $this->id = $id;
//         $this->name = $name;
//         $this->email = $email;
//         $this->birthDate = new DateTime($birthDate);
//     }
    
//     public function getId(): int { return $this->id; }
//     public function getName(): string { return $this->name; }
//     public function getEmail(): string { return $this->email; }
//     public function getAge(): int {
//         $now = new DateTime();
//         $interval = $this->birthDate->diff($now);
//         return $interval->y;
//     }
    
//     abstract public function getRole(): string;
//     abstract public function getDetails(): array;
    
//     public function displayInfo(): string {
//         return sprintf("%s (ID: %d, Email: %s, Вік: %d)", 
//             $this->name, $this->id, $this->email, $this->getAge());
//     }
// }

// // 2. КЛАС СТУДЕНТА
// // ============================================================================

// class Student extends Person {
//     private string $studentId;
//     private string $faculty;
//     private int $year;
//     private array $grades = [];
//     private float $scholarship = 0;
    
//     public function __construct(int $id, string $name, string $email, string $birthDate, 
//                                 string $studentId, string $faculty, int $year) {
//         parent::__construct($id, $name, $email, $birthDate);
//         $this->studentId = $studentId;
//         $this->faculty = $faculty;
//         $this->year = $year;
//     }
    
//     public function getRole(): string { return 'Студент'; }
    
//     public function addGrade(string $subject, int $grade, string $date): void {
//         $this->grades[] = [
//             'subject' => $subject,
//             'grade' => $grade,
//             'date' => $date,
//             'teacher' => null
//         ];
//     }
    
    


