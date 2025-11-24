# Elderly Care Platform 🧓💙

A comprehensive web-based platform for elderly cognitive health monitoring and mental wellness tracking.

## Features

### 🎮 Cognitive Games
- **Memory Match** - Test short-term memory with sequences
- **Attention Focus** - Find patterns among distractions
- **Reaction Time** - Measure response speed
- **Puzzle Solver** - Visual puzzles and pattern completion
- **Visual Memory** - Grid-based position memory (Human Benchmark inspired)
- **Number Memory** - Progressive digit span recall
- **Verbal Memory** - Word recognition memory test
- **Chimp Test** - Number sequencing working memory challenge

### 📊 Mental Health Assessments
Validated clinical questionnaires:
- **WHO-5** - Well-Being Index
- **GDS-15** - Geriatric Depression Scale
- **PHQ-9** - Patient Health Questionnaire (mood)
- **GAD-7** - Generalized Anxiety Disorder screening
- **PSS-4** - Perceived Stress Scale
- **PSQI** - Pittsburgh Sleep Quality Index

### 📈 Analytics & Insights
- Personal dashboard with performance tracking
- Mood diary with emotional logging
- Cognitive performance trends
- Mental wellness reports
- Game history and statistics

### 👤 User Management
- Secure authentication system
- User profiles with customization
- Settings and preferences
- High contrast mode for accessibility

## Tech Stack

- **Backend:** PHP 7.4+ with PDO
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Architecture:** MVC-inspired structure

## Project Structure

```
old-people/
├── assets/
│   ├── css/          # Modular stylesheets
│   ├── js/           # Game logic and interactions
│   └── images/       # Static images
├── database/
│   ├── config.php    # Database configuration
│   ├── init.php      # Database connection
│   ├── functions.php # Database helper functions
│   └── elder_care.sql # Database schema
├── games/            # Individual game files
├── pages/
│   ├── account/      # Authentication & user management
│   ├── emotion/      # Mood tracking & questionnaires
│   └── insights/     # Dashboard & reports
├── _header.php       # Common header
├── _footer.php       # Common footer
└── index.php         # Landing page

```

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or PHP built-in server

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <your-repo-url>
   cd old-people
   ```

2. **Import the database**
   ```bash
   mysql -u your_username -p your_database < database/elder_care.sql
   ```

3. **Add new games to database**
   ```bash
   mysql -u your_username -p your_database < database/add_new_games.sql
   ```

4. **Configure database connection**
   ```php
   // database/config.php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'elder_care');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

5. **Start the server**
   ```bash
   php -S localhost:8000
   ```

6. **Access the application**
   Open browser: `http://localhost:8000`

## Database Schema

### Main Tables
- `users` - User accounts and authentication
- `games` - Available cognitive games
- `game_sessions` - Game play sessions
- `game_scores` - Detailed game results
- `mood_logs` - Daily mood tracking
- `questionnaires` - Mental health assessment results
- `hobbies` - User hobbies and interests

## Security Features

- Password hashing with PHP `password_hash()`
- Prepared statements (PDO) to prevent SQL injection
- Session-based authentication
- CSRF protection considerations
- Input validation and sanitization

## Accessibility

- High contrast mode toggle
- Large, readable fonts
- Clear visual feedback
- Simple, intuitive navigation
- Elderly-friendly interface design

## Game Scoring

All games track:
- Score/points earned
- Time duration
- Accuracy percentage
- Difficulty level
- Date and timestamp

## Mental Health Assessments

All questionnaires use validated clinical scales:
- Evidence-based questions
- Standardized scoring
- Clinical interpretation
- Randomized question selection
- Privacy-compliant storage

## Contributing

This is a project for elderly care. Contributions welcome!

### Development Guidelines
- Follow existing code structure
- Comment complex logic
- Test on multiple browsers
- Consider accessibility in all features
- Maintain elderly-friendly design

## License

[Specify your license here]

## Credits

- Cognitive games inspired by Human Benchmark
- Clinical assessments based on validated instruments (WHO-5, GDS-15, PHQ-9, GAD-7, PSS-4, PSQI)

## Support

For issues or questions, please open an issue in the repository.

## Roadmap

### Planned Features
- [ ] Caregiver portal
- [ ] Mobile responsive improvements
- [ ] Data export for healthcare providers
- [ ] Medication reminders
- [ ] Social features for peer support
- [ ] AI-powered insights
- [ ] Voice interface option
- [ ] Multi-language support

---

**Built with ❤️ for elderly cognitive health and wellness**
