# Can You Beat Gemini? Tic Tac Toe

Welcome to the **Can You Beat Gemini? Tic Tac Toe** project! This is an interactive Tic-Tac-Toe game where you play against an AI opponent named Gemini(Gemini API). The AI is designed to challenge your strategic thinking and make your Tic-Tac-Toe games more interesting.

## Features

- **AI-Powered Opponent:** The AI, powered by Gemini, makes strategic moves to offer a robust challenge.
- **Real-Time Game State Updates:** The game board updates dynamically after each move, providing real-time visual feedback.
- **Voice and SMS Integration:** Use Twilio to handle user inputs via voice and SMS for an engaging experience.
- **Session Management:** Continuity in the game state across multiple moves using Laravel's session management.

## Prerequisites

- PHP 8.1 or higher
- Composer
- Laravel 11
- Twilio Account

## Installation

1. **Clone the repository:**

   ```bash
   git clone https://github.com/your-username/can-you-beat-gemini-tictactoe.git
   cd can-you-beat-gemini-tictactoe
   ```
2. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```
4. **Set up environment variables:**
   
   ```text
   TWILIO_SID=your_twilio_sid
   TWILIO_AUTH_TOKEN=your_twilio_auth_token
   TWILIO_PHONE_NUMBER=your_twilio_phone_number
   MY_PHONE_NUMBER=your_phone_number
   ```
5. **Generate an application key:**
   ```bash
   php artisan key:generate
   ```
6. **Start the Laravel server:**
   ```bash
   php artisan serve
   ```
7. **Use ngrok to expose your local server to the internet:**
   ```bash
   ngrok http 8000
   ```
8. **Set up your Twilio number:**

   - Log in to your Twilio Console.
   - Go to the Phone Numbers section and select your Twilio number.
   - Set the "A Call Comes In" webhook URL to http://your-ngrok-url/api/input.
9. **Playing the game:**
    -  Call your Twilio number to start the game.
    -  Follow the voice prompts or SMS responses to make your move by entering a number between 1 and 9.
    -  The AI will make a move after you.
    -  The game state will update after each move, and an image of the board will be sent to you via SMS.



