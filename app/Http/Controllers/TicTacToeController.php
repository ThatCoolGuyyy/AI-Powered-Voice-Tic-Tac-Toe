<?php

namespace App\Http\Controllers;

use Twilio\Rest\Client;
use Illuminate\Http\Request;
use Twilio\TwiML\VoiceResponse;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;

class TicTacToeController extends Controller
{
    private $response, $mappings, $client;

    public function __construct()
    {
        $this->response = new VoiceResponse();
        $this->client = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));

        $this->mappings = [
            '1' => 'A1', '2' => 'A2', '3' => 'A3',
            '4' => 'B1', '5' => 'B2', '6' => 'B3',
            '7' => 'C1', '8' => 'C2', '9' => 'C3',
        ];
    } 

    public function handleInput(Request $request)
    {
        $this->response->say('Welcome to Tic Tac Toe. Please enter a number between 1 and 9 to make your move.');

        $this->response->gather([
            'numDigits' => 1,
            'action' => '/api/process-move',
        ]);

        return $this->response;
    }

    public function handleVoiceProcess(Request $request)
    {
        if (!Cache::has('gameState')) {
            Cache::put('gameState', [
                'A1' => ' ', 'A2' => ' ', 'A3' => ' ',
                'B1' => ' ', 'B2' => ' ', 'B3' => ' ',
                'C1' => ' ', 'C2' => ' ', 'C3' => ' '
            ], 180);
        }

        $gameState = Cache::get('gameState');

        $digit = $request->input('Digits');
        $move = $this->mappings[$digit];
        $message = $this->processMove($move, env('MY_PHONE_NUMBER'), $gameState);

        $this->response->say($message);
        $this->response->gather([
            'numDigits' => 1,
            'action' => '/api/process-move',
        ]);

        return $this->response;
    }

    protected function processMove($move, $playerPhoneNumber, $gameState)
    {
        if (!$this->checkIfSpaceIsAvailable($gameState, $move)) {
            return "Invalid move. Please try again.";
        }

        $this->updateGameState($gameState, "$move = X");

        $result = $this->checkGameResult($gameState);
        if ($result) {
            $this->generateImageFromGameState($gameState);
            $this->sendSms($playerPhoneNumber, $result, url('tic_tac_toe_board.png'));
            Cache::forget('gameState');

            return $result;
        }

        $aiMove = $this->getAIMove($move, $gameState);

        $this->updateGameState($gameState, "$aiMove = O");
        $this->generateImageFromGameState($gameState);

        $result = $this->checkGameResult($gameState);
        if ($result) {

            $this->sendSms($playerPhoneNumber, $result, url('tic_tac_toe_board.png'));
            Cache::forget('gameState');
            return $result;
        }

        Cache::put('gameState', $gameState, 180);
        info(json_encode($gameState));

        $message = "You played $move. AI played $aiMove. What's your next move?";
        $this->sendSms($playerPhoneNumber, null, url('tic_tac_toe_board.png'));
        $this->sendSms($playerPhoneNumber, "You played $move. AI played $aiMove.");

        return $message;
    }

    public function sendSms($to, $message = null, $mediaUrl = null)
    {
        $messageData = [
            'from' => env('TWILIO_PHONE_NUMBER'),
        ];

        if ($mediaUrl) {
            $messageData['mediaUrl'] = [$mediaUrl];
        }

        if($message){
            $messageData['body'] = $message;
        }

        $this->client->messages->create(
            "whatsapp:" . $to,
            $messageData
        );
    }

    protected function updateGameState(&$gameState, $playerMove)
    {
        [$position, $value] = explode(' = ', $playerMove);
        $gameState[$position] = $value;
    }

    protected function checkGameResult($board)
    {
        $score = $this->evaluate($board);
        if ($score === 10) {
            return "AI wins! Game over.";
        } elseif ($score === -10) {
            return "You win! Game over.";
        } elseif (!in_array(' ', $board)) {
            return "It's a draw! Game over.";
        }
        return null;
    }

    protected function evaluate($board)
    {
        $winningCombinations = [
            ['A1', 'A2', 'A3'], ['B1', 'B2', 'B3'], ['C1', 'C2', 'C3'],
            ['A1', 'B1', 'C1'], ['A2', 'B2', 'C2'], ['A3', 'B3', 'C3'],
            ['A1', 'B2', 'C3'], ['A3', 'B2', 'C1'],
        ];

        foreach ($winningCombinations as $combination) {
            if ($board[$combination[0]] === $board[$combination[1]] && $board[$combination[1]] === $board[$combination[2]]) {
                if ($board[$combination[0]] === 'O') {
                    return 10;
                } elseif ($board[$combination[0]] === 'X') {
                    return -10;
                }
            }
        }
        return 0;
    }

    protected function getAIMove($playerMove, $gameState)
    {
        $result = Gemini::geminiPro()->generateContent("You're playing a game of tic tac toe with a human. The goal is to get three marks (X or O) in a row horizontally, vertically, or diagonally. The human plays X at " . $playerMove . ". If you can't win immediately, try to block the human from winning. What's your next move? Reply with just your answer (e.g., B2), current game state is " . json_encode($gameState));
        return $result->text();
    }

    protected function generateImageFromGameState($board)
    {
        $html = View::make('tic_tac_toe_board', ['board' => $board])->render();

        $imagePath = public_path('tic_tac_toe_board.png');

        Browsershot::html($html)->save($imagePath);

        $publicPath = 'public/tic_tac_toe_board.png';
        copy($imagePath, storage_path('app/' . $publicPath));
        $path = url('storage/' . $publicPath);

        return $path;
    }

    protected function checkIfSpaceIsAvailable($gameState, $move)
    {
        if ($gameState[$move] == " ") {
            return true;
        }

        return false;
    }
}
