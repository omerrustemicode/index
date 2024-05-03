<?php
// Function to load data from JSON file
function loadData() {
    // Read JSON file contents
    $json_data = file_get_contents('data.json');

    // Decode JSON data
    return json_decode($json_data, true);
}

// Function to load quotes from JSON file
function loadQuotes() {
    // Read JSON file contents
    $json_data = file_get_contents('quotes.json');

    // Decode JSON data
    return json_decode($json_data, true);
}

// Function to get a random quote
function getRandomQuote($quotes) {
    // Get the total number of quotes
    $total_quotes = count($quotes);

    // Generate a random index
    $random_index = mt_rand(0, $total_quotes - 1);

    // Return the random quote
    return $quotes[$random_index];
}

// Function to handle user queries and provide responses
function getResponse($query, $data, $quotes) {
    // Normalize user query to lowercase for easier matching
    $query = strtolower($query);

    // Remove symbols from the query
    $query = preg_replace('/[^\w\s]/', '', $query);

    // Check if the query exists in the data
    if (isset($data[$query])) {
        return $data[$query];
    } elseif (strpos($query, 'change color to') !== false) {
        // Extract the color from the user's query
        $color = str_replace('change color to ', '', $query);
        // Set the background color and button color
        echo "<script>updateUserColor('$color');</script>";
        setcookie('background_color', $color, time() + (86400 * 30), "/"); // Store color in cookie
        return "Color changed to $color.";
    } else {
        // Log questions without answers
        logQuestion($query);
        // Get a random quote as feedback
        $random_quote = getRandomQuote($quotes);
        return "I'm sorry, I couldn't understand your question. Here's a random quote for you: <br><i>'" . $random_quote['quote'] . "'</i> - " . $random_quote['author'];
    }
}

// Function to log questions without answers
function logQuestion($query) {
    $log_file = 'unanswered_questions.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] Unanswered question: $query\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Function to log color change requests
function logColorChange($color) {
    $log_file = 'color_change.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] Color changed to $color\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Function to display chatbot response
function chatBot() {
    // Load data from JSON file
    $data = loadData();

    // Load quotes from JSON file
    $quotes = loadQuotes();

    // Display a random quote
    $random_quote = getRandomQuote($quotes);

    // Get the user input from POST request
    $user_input = isset($_POST['user_input']) ? $_POST['user_input'] : '';

    // Check if user input is provided
    if (!empty($user_input)) {
        // Pass the user input as is to the getResponse function
        // without any modification
        $response = getResponse($user_input, $data, $quotes);

        // Display the bot message including the response
        echo "<div class='bot-message'><b>OmerAI</b>: $response</div>";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Chatbot</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: <?php echo isset($_COOKIE['background_color']) ? $_COOKIE['background_color'] : '#f5f5f5'; ?>;
            transition: background-color 0.5s ease;
        }

        .chat-container {
            width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: background-color 0.5s ease;
            perspective: 1000px;
        }

        .bot-message {
            background-color: #eaeaea;
            color: #333;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
            transform-style: preserve-3d;
            transform: translateZ(25px);
            transition: transform 0.5s;
        }

        .bot-quotes {
            background-color: #eaeaea;
            color: #5858D4;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
        }

        .user-input {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            margin-right: 20px;
            display: inline-block;
        }

        .submit-btn {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }

        h3 {
            text-align: center;
            margin-bottom: 20px;
            transform-style: preserve-3d;
            transform: translateZ(25px);
            transition: transform 0.5s;
        }
    </style>
    <script>
        function updateQuote() {
            fetch('quotes.json')
                .then(response => response.json())
                .then(quotes => {
                    const randomIndex = Math.floor(Math.random() * quotes.length);
                    const randomQuote = quotes[randomIndex];
                    document.getElementById('quote').innerHTML = `<div class='bot-quotes'>${randomQuote.quote}<br>- ${randomQuote.author}</div>`;
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            updateQuote();
            setInterval(updateQuote, 10000); // Update quote every 10 seconds
        });
    </script>
</head>
<body>
<div class="chat-container">
    <h3><div id="quote"></div></h3>
   
    <?php chatBot(); ?>
    <form method="POST" action="">
        <input type="text" id="user_input" name="user_input" class="user-input" placeholder="Type your question here...">
        <input type="submit" id="submit-btn" value="Submit" class="submit-btn">
    </form>
</div>
</body>
</html>

