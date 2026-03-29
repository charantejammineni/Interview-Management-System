<?php
// Include the Session class and establish user authentication
include("inc/classes/session.php");
$userSession = new Session();

// Restrict access to logged-in students only
if ($userSession->getSession('login') != true || $userSession->getSession('role') != 'student') {
    header('Location: login.php');
    exit();
}

// --- PSYCHOMETRIC QUESTION BANK ---
// An array containing all 60 psychometric questions and their options.
$questionBank = [
    ['q' => 'When starting a new project, I am most likely to:', 'o' => ['Create a detailed plan and timeline first.', 'Jump right in and figure things out as I go.', 'Brainstorm a wide range of creative ideas.', 'Discuss the goals with team members to get aligned.']],
    ['q' => 'I make my best decisions when I:', 'o' => ['Rely on data and logical analysis.', 'Trust my intuition and gut feeling.', 'Consider the impact on people and relationships.', 'Follow established procedures and rules.']],
    ['q' => 'In a social gathering, I am more likely to:', 'o' => ['Stay on the sidelines and observe.', 'Engage in deep conversation with one or two people.', 'Be the life of the party, moving between groups.', 'Help the host and make sure everyone is comfortable.']],
    ['q' => 'When faced with a sudden, unexpected problem, I:', 'o' => ['Remain calm and systematically assess the situation.', 'Feel a rush of excitement and adapt quickly.', 'Feel stressed and need a moment to think.', 'Immediately look for someone to collaborate with.']],
    ['q' => 'I feel most satisfied at work when I:', 'o' => ['Have completed my tasks efficiently and accurately.', 'Have created something new and innovative.', 'Have helped a colleague or contributed to team success.', 'Have followed a process correctly from start to finish.']],
    ['q' => 'My workspace is typically:', 'o' => ['Highly organized and tidy.', 'A bit chaotic but I know where everything is.', 'Decorated with personal and inspiring items.', 'Functional and minimalistic.']],
    ['q' => 'When learning a new skill, I prefer:', 'o' => ['A structured course with clear instructions.', 'Experimenting and learning through trial and error.', 'A workshop or group setting where I can learn with others.', 'Watching a demonstration by an expert.']],
    ['q' => 'I am most motivated by:', 'o' => ['Achieving measurable results and goals.', 'The freedom to be creative and autonomous.', 'Recognition and praise from my peers and superiors.', 'A sense of stability and job security.']],
    ['q' => 'When receiving feedback, I prefer it to be:', 'o' => ['Direct, objective, and based on facts.', 'Encouraging and focused on my potential.', 'Delivered gently and with consideration for my feelings.', 'A collaborative discussion about improvement.']],
    ['q' => 'My ideal weekend involves:', 'o' => ['Catching up on tasks and organizing for the week ahead.', 'Spontaneous adventures with no fixed plan.', 'Spending quality time with friends and family.', 'Relaxing at home with a book or movie.']],
    ['q' => 'In a team conflict, my role is often to:', 'o' => ['Propose a logical solution based on the facts.', 'Suggest a compromise that makes everyone feel heard.', 'Lighten the mood and reduce tension.', 'Stay out of it unless directly involved.']],
    ['q' => 'I prefer tasks that are:', 'o' => ['Clear, predictable, and consistent.', 'Varied, challenging, and dynamic.', 'Collaborative and involve teamwork.', 'Independent and allow me to work alone.']],
    ['q' => 'When communicating, I tend to be more:', 'o' => ['Concise and to the point.', 'Expressive and enthusiastic.', 'A thoughtful and careful listener.', 'Diplomatic and tactful.']],
    ['q' => 'For long-term goals, I am better at:', 'o' => ['Sticking to the original plan.', 'Adapting the plan as circumstances change.', 'Keeping the team motivated and focused.', 'Focusing on the big-picture vision.']],
    ['q' => 'I am more concerned with:', 'o' => ['Whether something is logical and efficient.', 'Whether something is fair and just for everyone.', 'Whether something is new and exciting.', 'Whether something is safe and reliable.']],
    ['q' => 'When under pressure, I am most likely to:', 'o' => ['Become more focused and detail-oriented.', 'Withdraw to think and process.', 'Seek support and talk it through with others.', 'Work faster and multitask.']],
    ['q' => 'I find it easier to:', 'o' => ['Notice details and specifics.', 'See the overall pattern and connections.', 'Understand people\'s emotions and motivations.', 'Follow a step-by-step guide.']],
    ['q' => 'Change, for me, is generally:', 'o' => ['An opportunity for growth and excitement.', 'Unsettling and requires a period of adjustment.', 'Something to be managed with a careful plan.', 'A necessary part of progress.']],
    ['q' => 'I am more drawn to careers that offer:', 'o' => ['Stability, structure, and clear expectations.', 'Creativity, flexibility, and innovation.', 'A chance to help others and make a difference.', 'Leadership, influence, and strategic challenges.']],
    ['q' => 'When completing a task, I prioritize:', 'o' => ['Meeting the deadline, no matter what.', 'Ensuring the highest possible quality, even if it takes longer.', 'Making sure the process is collaborative and inclusive.', 'Finding the most efficient and clever way to do it.']],
    ['q' => 'My approach to risk is:', 'o' => ['Cautious and calculated.', 'Bold and optimistic.', 'To avoid it whenever possible.', 'Dependent on the potential reward.']],
    ['q' => 'I feel drained after:', 'o' => ['Spending a lot of time in a large group.', 'Dealing with repetitive, mundane tasks.', 'Having to focus on small details for too long.', 'A day with no clear structure or plan.']],
    ['q' => 'When I disagree with someone, I am most likely to:', 'o' => ['State my position with facts and logic.', 'Try to find common ground.', 'Avoid confrontation and change the subject.', 'Listen to their side completely before responding.']],
    ['q' => 'I am best described as:', 'o' => ['Realistic and practical.', 'Imaginative and visionary.', 'Empathetic and compassionate.', 'Dependable and responsible.']],
    ['q' => 'I prefer to work in an environment that is:', 'o' => ['Quiet and allows for deep concentration.', 'Bustling with energy and activity.', 'Supportive and team-oriented.', 'Organized and predictable.']],
    ['q' => 'When making a purchase, I focus on:', 'o' => ['The product\'s features and functionality.', 'The brand\'s reputation and quality.', 'How the purchase will make me or others feel.', 'Getting the best possible price or value.']],
    ['q' => 'My first reaction to a new rule is to:', 'o' => ['Understand the logic behind it.', 'Question if it\'s necessary.', 'Consider how it will affect people.', 'Accept it and adapt accordingly.']],
    ['q' => 'I enjoy stories that are:', 'o' => ['Fact-based and historical.', 'Fantastical and explore new worlds.', 'Character-driven and emotional.', 'Mysteries with a clear resolution.']],
    ['q' => 'When setting personal goals, I make them:', 'o' => ['Specific, measurable, and realistic.', 'Ambitious and inspiring.', 'Flexible and open to change.', 'Aligned with my core values.']],
    ['q' => 'I am more of a:', 'o' => ['Thinker.', 'Doer.', 'Feeler.', 'Planner.']],
    ['q' => 'In a debate, my strength is:', 'o' => ['Constructing a logical argument.', 'Thinking of creative counter-arguments.', 'Understanding the other person\'s perspective.', 'Sticking to the facts and evidence.']],
    ['q' => 'I am most uncomfortable when:', 'o' => ['Things are disorganized and chaotic.', 'I have to follow a rigid, inflexible routine.', 'There is conflict or tension in a group.', 'I don\'t have enough information to proceed.']],
    ['q' => 'My friends would say I am:', 'o' => ['The reliable one.', 'The spontaneous one.', 'The good listener.', 'The organized one.']],
    ['q' => 'I prefer to spend my free time:', 'o' => ['Learning something new or working on a hobby.', 'Exploring new places and trying new things.', 'Connecting with friends and loved ones.', 'Organizing my home or planning my week.']],
    ['q' => 'When a friend has a problem, I first:', 'o' => ['Offer practical solutions and advice.', 'Just listen and offer emotional support.', 'Share a similar experience I had.', 'Ask questions to understand the situation better.']],
    ['q' => 'I am more impressed by:', 'o' => ['Someone\'s technical skill and expertise.', 'Someone\'s creativity and original ideas.', 'Someone\'s kindness and integrity.', 'Someone\'s discipline and work ethic.']],
    ['q' => 'I prefer a boss who:', 'o' => ['Is competent and gives clear directions.', 'Is inspiring and trusts me to innovate.', 'Is supportive and cares about my well-being.', 'Is fair and consistent with everyone.']],
    ['q' => 'When I have to wait, I usually:', 'o' => ['Become impatient and restless.', 'Find something to read or do on my phone.', 'Strike up a conversation with someone nearby.', 'Mentally review my to-do list.']],
    ['q' => 'My decision-making is most influenced by:', 'o' => ['Logic and reason.', 'Future possibilities.', 'Personal values.', 'Past experiences.']],
    ['q' => 'I am energized by:', 'o' => ['Solving a complex, analytical problem.', 'Starting a new creative endeavor.', 'A meaningful conversation with a friend.', 'Checking items off my to-do list.']],
    ['q' => 'When working, I dislike:', 'o' => ['Vague instructions and unclear expectations.', 'Being micromanaged and lacking freedom.', 'Working in a competitive, non-cooperative environment.', 'Frequent interruptions and distractions.']],
    ['q' => 'I am more likely to trust someone who is:', 'o' => ['Knowledgeable and can back up their claims.', 'Passionate and believes in their ideas.', 'Authentic and shows their true feelings.', 'Consistent and has a proven track record.']],
    ['q' => 'I prefer movies that:', 'o' => ['Make me think.', 'Inspire me.', 'Make me feel.', 'Are based on true events.']],
    ['q' => 'My approach to deadlines is:', 'o' => ['I finish work well in advance.', 'I do my best work under pressure at the last minute.', 'I plan my work to finish just on time.', 'Deadlines stress me out, but I always meet them.']],
    ['q' => 'I would rather be seen as:', 'o' => ['Intelligent.', 'Creative.', 'Caring.', 'Dependable.']],
    ['q' => 'When planning a vacation, I prefer to:', 'o' => ['Have a detailed itinerary for each day.', 'Have a general idea but leave room for spontaneity.', 'Go wherever my travel partners want to go.', 'Choose a familiar and relaxing destination.']],
    ['q' => 'I am better at remembering:', 'o' => ['Facts and figures.', 'Concepts and theories.', 'People\'s faces and names.', 'Step-by-step processes.']],
    ['q' => 'I am most annoyed by:', 'o' => ['Inefficiency and illogical arguments.', 'Close-mindedness and lack of imagination.', 'Insensitivity and thoughtlessness.', 'Unpredictability and lack of order.']],
    ['q' => 'My ideal job involves:', 'o' => ['Analyzing data and finding patterns.', 'Designing new products or systems.', 'Counseling or teaching others.', 'Managing projects and resources.']],
    ['q' => 'I find it easy to:', 'o' => ['Stay objective and detached in a debate.', 'See the hidden potential in ideas.', 'Empathize with how others are feeling.', 'Create and follow a structured plan.']],
    ['q' => 'When I am in a new environment, I first:', 'o' => ['Observe and try to understand how things work.', 'Look for exciting possibilities and things to explore.', 'Try to connect with people and make friends.', 'Look for the essential facilities and information.']],
    ['q' => 'I am more likely to be swayed by:', 'o' => ['A logical argument with strong evidence.', 'A compelling vision of the future.', 'A heartfelt personal story.', 'A recommendation from a trusted authority.']],
    ['q' => 'I feel successful when:', 'o' => ['I have mastered a complex skill.', 'I have brought a unique idea to life.', 'I have made a positive impact on someone.', 'I have achieved a difficult, long-term goal.']],
    ['q' => 'I prefer to communicate via:', 'o' => ['Email, so I have a written record.', 'Face-to-face, to see body language and reactions.', 'Phone call, for a quick and direct conversation.', 'Whatever is most efficient for the situation.']],
    ['q' => 'I am most interested in:', 'o' => ['How things work.', 'What things could be.', 'Who is involved.', 'What the plan is.']],
    ['q' => 'When I have free time, I often find myself:', 'o' => ['Organizing something.', 'Daydreaming or brainstorming.', 'Calling or texting a friend.', 'Reading non-fiction or watching documentaries.']],
    ['q' => 'My leadership style is to:', 'o' => ['Lead by example with logic and competence.', 'Inspire the team with a compelling vision.', 'Lead with empathy and support for my team.', 'Manage the process and ensure everyone is on track.']],
    ['q' => 'I am most likely to remember a mistake if:', 'o' => ['It was a logical error I should have caught.', 'It limited a creative opportunity.', 'It hurt someone\'s feelings.', 'It disrupted a well-laid plan.']],
    ['q' => 'I am drawn to beauty in:', 'o' => ['Symmetry, structure, and elegant design.', 'Originality, complexity, and abstract forms.', 'Nature and human expression.', 'Simplicity, functionality, and craftsmanship.']],
    ['q' => 'I recharge my energy by:', 'o' => ['Spending time alone with my thoughts.', 'Engaging in a creative or novel activity.', 'Spending quality time with close friends.', 'Completing a task and bringing order to my space.']],
];

// --- PSYCHOMETRIC ANSWER KEY ---
// A mapping of each question's correct/desired answers to a specific trait (A, B, C, D)
// This is a simplified example; a real assessment would be more complex.
// Traits: A=Analytical, B=Creative, C=Empathetic, D=Organized
$psychometric_key = [
    1 => ['a' => 'D', 'b' => 'B', 'c' => 'B', 'd' => 'C'],
    2 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    3 => ['a' => 'A', 'b' => 'C', 'c' => 'B', 'd' => 'D'],
    4 => ['a' => 'D', 'b' => 'B', 'c' => 'A', 'd' => 'C'],
    5 => ['a' => 'D', 'b' => 'B', 'c' => 'C', 'd' => 'A'],
    6 => ['a' => 'D', 'b' => 'B', 'c' => 'C', 'd' => 'A'],
    7 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    8 => ['a' => 'D', 'b' => 'B', 'c' => 'C', 'd' => 'A'],
    9 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    10 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    11 => ['a' => 'A', 'b' => 'C', 'c' => 'B', 'd' => 'D'],
    12 => ['a' => 'D', 'b' => 'B', 'c' => 'C', 'd' => 'A'],
    13 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    14 => ['a' => 'D', 'b' => 'B', 'c' => 'C', 'd' => 'A'],
    15 => ['a' => 'A', 'b' => 'C', 'c' => 'B', 'd' => 'D'],
    16 => ['a' => 'A', 'b' => 'D', 'c' => 'C', 'd' => 'B'],
    17 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    18 => ['a' => 'B', 'b' => 'D', 'c' => 'A', 'd' => 'D'],
    19 => ['a' => 'D', 'b' => 'B', 'c' => 'C', 'd' => 'A'],
    20 => ['a' => 'D', 'b' => 'A', 'c' => 'C', 'd' => 'B'],
    21 => ['a' => 'A', 'b' => 'B', 'c' => 'D', 'd' => 'C'],
    22 => ['a' => 'C', 'b' => 'A', 'c' => 'D', 'd' => 'B'],
    23 => ['a' => 'A', 'b' => 'C', 'c' => 'B', 'd' => 'D'],
    24 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    25 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    26 => ['a' => 'A', 'b' => 'D', 'c' => 'C', 'd' => 'A'],
    27 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    28 => ['a' => 'D', 'b' => 'B', 'c' => 'C', 'd' => 'A'],
    29 => ['a' => 'A', 'b' => 'B', 'c' => 'B', 'd' => 'C'],
    30 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    31 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    32 => ['a' => 'D', 'b' => 'B', 'c' => 'C', 'd' => 'A'],
    33 => ['a' => 'D', 'b' => 'B', 'c' => 'C', 'd' => 'A'],
    34 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    35 => ['a' => 'A', 'b' => 'C', 'c' => 'B', 'd' => 'D'],
    36 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    37 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    38 => ['a' => 'B', 'b' => 'A', 'c' => 'C', 'd' => 'D'],
    39 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    40 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    41 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    42 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    43 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    44 => ['a' => 'D', 'b' => 'B', 'c' => 'D', 'd' => 'A'],
    45 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    46 => ['a' => 'D', 'b' => 'B', 'c' => 'C', 'd' => 'A'],
    47 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    48 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    49 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    50 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    51 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    52 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    53 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    54 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    55 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    56 => ['a' => 'D', 'b' => 'B', 'c' => 'C', 'd' => 'A'],
    57 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    58 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
    59 => ['a' => 'D', 'b' => 'B', 'c' => 'C', 'd' => 'A'],
    60 => ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'],
];

// --- LOGIC ---
// Check if the form was submitted
$submission_message = '';
$show_quiz = true;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $show_quiz = false;

    // Connect to the database
    include_once("inc/classes/db.php");
    $db = new Database();
    $conn = $db->connect();

    if ($conn) {
        $studentId = $userSession->getSession('user_id');
        $traitScores = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0];

        // Process submitted answers
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'q') === 0 && is_numeric(substr($key, 1))) {
                $questionNumber = (int)substr($key, 1);
                
                // Get the trait for the selected answer
                if (isset($psychometric_key[$questionNumber][$value])) {
                    $trait = $psychometric_key[$questionNumber][$value];
                    $traitScores[$trait]++;
                }
            }
        }
        
        // Let's define the ideal profile as high scores in 'A' (Analytical) and 'D' (Organized).
        $desiredTraits = ['A', 'D'];
        $totalDesiredAnswers = 0;
        
        foreach ($desiredTraits as $trait) {
            $totalDesiredAnswers += $traitScores[$trait];
        }

        // Calculate the psychometric score as a percentage of the 20 questions
        $psychometric_score = ($totalDesiredAnswers / 20) * 100;
        
        // Store the score in the database
        $sql = "UPDATE ims_performance_scores SET psychometric_score = ? WHERE student_id = ?";
        
        // Check for existing record, if not, insert one.
        $check_sql = "SELECT student_id FROM ims_performance_scores WHERE student_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $studentId);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            // Update the existing record
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("di", $psychometric_score, $studentId);
            if ($stmt->execute()) {
                $submission_message = "<div class='alert alert-success'>✅ Thank you for completing the quiz! Your responses have been recorded and your psychometric score is **" . round($psychometric_score, 2) . "%**.</div>";
            } else {
                $submission_message = "<div class='alert alert-danger'>❌ Error updating score: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            // Insert a new record
            $insert_sql = "INSERT INTO ims_performance_scores (student_id, psychometric_score) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("id", $studentId, $psychometric_score);
             if ($insert_stmt->execute()) {
                $submission_message = "<div class='alert alert-success'>✅ Thank you for completing the quiz! Your responses have been recorded and your psychometric score is **" . round($psychometric_score, 2) . "%**.</div>";
            } else {
                $submission_message = "<div class='alert alert-danger'>❌ Error inserting score: " . $insert_stmt->error . "</div>";
            }
            $insert_stmt->close();
        }

        $check_stmt->close();
        $conn->close();
    } else {
        $submission_message = "<div class='alert alert-danger'>❌ Database connection failed. Please try again later.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Psychometric Quiz</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .question-block {
            margin-bottom: 25px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .question-block h4 {
            margin-top: 0;
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php include('nav.php'); ?>

<div class="container" style="margin-top: 50px;">
    
    <?php if (!empty($submission_message)) echo $submission_message; ?>

    <?php if ($show_quiz): ?>
        <h2>Psychometric Quiz</h2>
        <p>Please answer the following questions honestly. Select the option that best describes you.</p>
        <hr>

        <form action="quiz.php" method="POST">
            <?php
            // Shuffle the question bank to randomize the order
            shuffle($questionBank);
            // Select the first 20 questions for the quiz
            $quizQuestions = array_slice($questionBank, 0, 20);

            // Loop through the 20 selected questions
            foreach ($quizQuestions as $index => $questionData) {
                // Find the original index from the full question bank to match with the answer key
                $originalIndex = array_search($questionData, $questionBank);
                $question_number = $originalIndex + 1; // Use original index + 1 for the key lookup

                $question_text = htmlspecialchars($questionData['q']);
                $options = $questionData['o'];
                $input_name = 'q' . $question_number;

                echo "<div class='question-block'>";
                echo "<h4>{$question_number}. {$question_text}</h4>";
                
                // Loop through the options for the current question
                foreach ($options as $option_index => $option_text) {
                    $option_value = chr(97 + $option_index); // a, b, c, d
                    $option_text_safe = htmlspecialchars($option_text);

                    echo "<div class='radio'>";
                    echo "<label><input type='radio' name='{$input_name}' value='{$option_value}' required> {$option_text_safe}</label>";
                    echo "</div>";
                }
                echo "</div>";
            }
            ?>
            
            <button type="submit" class="btn btn-primary btn-lg">Submit Answers</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>