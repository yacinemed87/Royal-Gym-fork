const members = [
    { id: 1, name: "Ahmed Benali", gender: "Male", email: "ahmed.benali@gmail.com", phone: "0551234567", plan: "Royal", joinDate: "2024-01-15" },
    { id: 2, name: "Yasmine Khelifi", gender: "Female", email: "yasmine.khelifi@gmail.com", phone: "0661987432", plan: "Elite", joinDate: "2024-03-22" },
    { id: 3, name: "Karim Boukhelifa", gender: "Male", email: "karim.boukhelifa@outlook.com", phone: "0773456789", plan: "Starter", joinDate: "2024-05-10" },
    { id: 4, name: "Nour El Houda Messaoud", gender: "Female", email: "nour.messaoud@gmail.com", phone: "0554321876", plan: "Royal", joinDate: "2023-11-30" },
    { id: 5, name: "Riad Tlemcani", gender: "Male", email: "riad.tlemcani@yahoo.fr", phone: "0698765432", plan: "Elite", joinDate: "2024-02-08" },
    { id: 6, name: "Amira Zouaoui", gender: "Female", email: "amira.zouaoui@gmail.com", phone: "0771122334", plan: "Starter", joinDate: "2024-06-01" },
    { id: 7, name: "Sofiane Hadj Amar", gender: "Male", email: "sofiane.hadjamar@gmail.com", phone: "0559988776", plan: "Royal", joinDate: "2023-09-14" },
    { id: 8, name: "Lina Belarbi", gender: "Female", email: "lina.belarbi@gmail.com", phone: "0662233445", plan: "Elite", joinDate: "2024-04-19" },
];

const membershipPlans = [
    {
        id: 1,
        name: "Starter",
        price: 4900,
        duration: "1 month",
        features: ["3 gym & 1 cardio visits/week", "Premium equipment access", "Standard locker", "Standard support"]
    },
    {
        id: 2,
        name: "Elite",
        price: 14900,
        duration: "1 month",
        features: ["All Starter benefits", "5 gym & 5 cardio visits/week", "Sauna & Steam rooms", "Meal plan", "Priority support"]
    },
    {
        id: 3,
        name: "Royal",
        price: 24900,
        duration: "1 month",
        features: ["All Elite benefits", "Unlimited access", "Personal Training Zone", "Monthly 1-on-1 coaching", "Massage services", "Dedicated concierge"]
    }
];

const trainers = [
    // --- SPECIALTY PAIRS (6 Males / 4 Females) ---
    { id: 1, name: "Alikhodja Yacine", specialty: "Strength Training", gender: "Male", experience: 8, bio: "Expert in muscle building." },
    { id: 2, name: "Sara Meziani", specialty: "Strength Training", gender: "Female", experience: 6, bio: "Specialist in body toning." },

    { id: 3, name: "Achour Iskander", specialty: "Cardio Fitness", gender: "Male", experience: 5, bio: "Endurance and heart health expert." },
    { id: 4, name: "Meriem Haddad", specialty: "Cardio Fitness", gender: "Female", experience: 4, bio: "Aerobic conditioning specialist." },

    { id: 5, name: "Djaber Mark", specialty: "Personal Training", gender: "Male", experience: 7, bio: "Custom goal-based fitness plans." },
    { id: 6, name: "Selma Belkaid", specialty: "Personal Training", gender: "Female", experience: 5, bio: "One-on-one lifestyle coaching." },

    { id: 7, name: "Galileo Paul", specialty: "CrossFit", gender: "Male", experience: 6, bio: "Functional movement expert." },
    { id: 8, name: "Ines Rahmani", specialty: "CrossFit", gender: "Female", experience: 5, bio: "Cross-functional performance coach." },

    // Additional Male Trainers 
    { id: 9, name: "Tarek Mansouri", specialty: "Boxing", gender: "Male", experience: 10, bio: "Professional boxing technique." }
];

const maleClasses = [
    { id: 1, name: "Strength Training", trainer: "Alikhodja Yacine", day: "Monday", time: "09:00", duration: 60, difficulty: "Beginner" },
    { id: 3, name: "Cardio Fitness", trainer: "Achour Iskander", day: "Wednesday", time: "18:00", duration: 50, difficulty: "Beginner" },
    { id: 5, name: "Personal Training", trainer: "Djaber Mark", day: "Friday", time: "10:00", duration: 55, difficulty: "Beginner" },
    { id: 6, name: "Stretching & Recovery", trainer: "Walid Cherif", day: "Sunday", time: "11:00", duration: 45, difficulty: "Beginner" },
    { id: 4, name: "Boxing", trainer: "Tarek Mansouri", day: "Thursday", time: "17:00", duration: 60, difficulty: "Intermediate" },
    { id: 2, name: "CrossFit", trainer: "Galileo Paul", day: "Tuesday", time: "07:00", duration: 45, difficulty: "Advanced" },
    { id: 7, name: "Power Lifting", trainer: "Alikhodja Yacine", day: "Monday", time: "18:00", duration: 75, difficulty: "Advanced" },
    { id: 8, name: "Advanced Boxing", trainer: "Tarek Mansouri", day: "Saturday", time: "10:00", duration: 90, difficulty: "Advanced" }
];

const femaleClasses = [
    { id: 1, name: "Strength Training", trainer: "Alikhodja Yacine", day: "Monday", time: "09:00", duration: 60, difficulty: "Beginner" },
    { id: 3, name: "Cardio Fitness", trainer: "Achour Iskander", day: "Wednesday", time: "18:00", duration: 50, difficulty: "Beginner" },
    { id: 5, name: "Personal Training", trainer: "Djaber Mark", day: "Friday", time: "10:00", duration: 55, difficulty: "Beginner" },
    { id: 6, name: "Stretching & Recovery", trainer: "Walid Cherif", day: "Sunday", time: "11:00", duration: 45, difficulty: "Beginner" },
    { id: 4, name: "Boxing", trainer: "Tarek Mansouri", day: "Thursday", time: "17:00", duration: 60, difficulty: "Intermediate" },
    { id: 2, name: "CrossFit", trainer: "Galileo Paul", day: "Tuesday", time: "07:00", duration: 45, difficulty: "Advanced" },
    { id: 7, name: "Power Lifting", trainer: "Alikhodja Yacine", day: "Monday", time: "18:00", duration: 75, difficulty: "Advanced" },
    { id: 8, name: "Advanced Boxing", trainer: "Tarek Mansouri", day: "Saturday", time: "10:00", duration: 90, difficulty: "Advanced" }
];

export { members, membershipPlans, trainers, maleClasses, femaleClasses };
