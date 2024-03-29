<?php

use Instrumental\Models\LessonModel;
use Symfony\Component\Translation\Dumper\MoFileDumper;

the_post();

$teacherId = get_the_author_meta('ID'); // Get teacher ID ?
$user = wp_get_current_user();
$userId = $user->ID; // Get current user ID

$lessonModel = new LessonModel();

function setLessonStatus($lessonId, $status)
{
    $statusUpdate = new LessonModel();
    $statusUpdate->updateStatus($lessonId, $status);
}

$lessons = $lessonModel->getLessonsByTeacherId($userId);

if (in_array('teacher', $user->roles)) {

    $lessons = $lessonModel->getLessonsByTeacherId($userId);

    foreach ($lessons as $lesson) {
        if (array_key_exists('agree' . $lesson->lesson_id, $_POST)) {
            setLessonStatus($lesson->lesson_id, 1);
            header('Location: ?');
            exit();
        } elseif (array_key_exists('disagree' . $lesson->lesson_id, $_POST)) {
            setLessonStatus($lesson->lesson_id, 2);
            header('Location: ?');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= get_bloginfo('language'); ?>">

<head>
    <!-- wp header -->
    <?php get_header(); ?>
</head>

<body>
    <!-- Navigation-->
    <?php get_template_part('partials/navbar.tpl'); ?>

    <!-- Header-->
    <?php get_template_part('partials/header.tpl'); ?>

    <div class="container">

        <!-- Teacher Part -->
        <?php
        $roles = $user->roles;
        if (in_array('teacher', $roles)) :
            $isTeacher = true;
        else :
            $isTeacher = false;
        endif;

        if ($isTeacher) : ?>
            <section class="text-center">
                <h1 class="m-5">
                    <?php if (is_user_logged_in()) :
                        $user = wp_get_current_user(); ?>
                        <p>Bonjour <?= $user->display_name; ?></p>
                    <?php endif; ?>
                </h1>
            </section>

            <div class="text-center">
                <!-- Teacher avatar -->
                <?php $avatar = get_field('avatar', 'user_' . $user->ID); ?>
                <?php if ($avatar) : ?>
                    <img src="<?= $avatar['url'] ?>" />
                <?php endif; ?>
            </div>

            <div>
                <?php global $router;
                $updateProfileURL = $router->generate('user-update-profile'); ?>
                <p class="text-end mx-5"><a class="fs-5 text-end linkProfile" href="<?= $updateProfileURL ?>">Modifier votre profil</a></p>
            </div>

            <section class="m-5 descriptionPerso">
                <div class="container containerRecap">

                    <!-- New appointment -->
                    <ul class="recap m-8">
                        <h3>Vos nouvelles demandes de RDV</h3>

                        <?php if (in_array('teacher', $user->roles)) :

                            $lessons = $lessonModel->getLessonsByTeacherId($userId); ?>
                            <?php foreach ($lessons as $lesson) : ?>

                                <?php if ($lesson->status == 0) : ?>
                                    <li class="userProfileLi"> <?= $lesson->student->data->user_nicename ?> / <?= $lesson->appointment ?> / <?= $lesson->instrument->name ?></li>
                                    <form method="post" action="?q=<?= uniqid() ?>">
                                        <input type="submit" name="agree<?= $lesson->lesson_id ?>" class="btn btn-success" value="Valider" />
                                        <input type="submit" name="disagree<?= $lesson->lesson_id ?>" class="btn btn-danger" value="Refuser" />
                                    </form>
                            <?php endif;

                            endforeach; ?>

                        <?php endif; ?>
                    </ul>

                    <ul class="recap m-8">
                        <!-- Lesson list -->
                        <h3>Liste de vos cours</h3>

                        <?php if (in_array('teacher', $user->roles)) :
                            $lessons = $lessonModel->getLessonsByTeacherId($userId); ?>
                            <?php foreach ($lessons as $lesson) : ?>
                                <?php if ($lesson->status == 1) : ?>
                                    <li class="userProfileLi"> <?= $lesson->student->data->user_nicename ?> / <?= $lesson->appointment ?></li>
                            <?php endif;
                            endforeach; ?>
                        <?php endif; ?>
                    </ul>

                    <ul class="recap m-8">
                        <!-- Student list -->
                        <h3>Liste de vos élèves</h3>
                        <?php if (in_array('teacher', $user->roles)) :
                            $lessons = $lessonModel->getLessonsByTeacherId($userId); ?>
                            <?php foreach ($lessons as $lesson) : ?>
                                <?php if ($lesson->status == 1) : ?>

                                    <li class="userProfileLi"><?= $lesson->student->data->user_nicename ?> / <?= $lesson->instrument->name ?> / <?= $lesson->student->data->user_email ?></li>

                            <?php endif;
                            endforeach; ?>
                        <?php endif; ?>
                    </ul>

            </section>

            <!-- Student Part -->
        <?php else :

            $lessons = $lessonModel->getLessonsByStudentId($userId);
        ?>
            <section class="text-center">
                <h1 class="m-5">
                    <?php if (is_user_logged_in()) :
                        $user = wp_get_current_user(); ?>
                        <p>Bonjour <?= $user->display_name; ?> </p>
                        <!-- <?php dump($user); ?> -->
                    <?php endif; ?>
                </h1>
            </section>
            <div class="text-center">
                <!-- Student avatar -->
                <?php $avatar = get_field('avatar', 'user_' . $user->ID);
                if ($avatar) : ?>
                    <img src="<?= $avatar['url'] ?>" />
                <?php endif; ?>
            </div>
            <div>
                <?php global $router;
                $updateProfileURL = $router->generate('user-update-profile'); ?>
                <p class="text-end mx-5"><a class="fs-5 text-end linkProfile" href="<?= $updateProfileURL ?>">Modifier votre profil</a></p>
            </div>

            <section class="m-5 descriptionPerso">
                <div class="container containerRecap">

                    <ul class="recap m-5">
                        <!-- Lesson list -->
                        <h3>Vos prochains cours</h3>
                        <?php

                        if (in_array('student', $user->roles)) :
                            $lessons = $lessonModel->getLessonsByStudentId($userId);
                            foreach ($lessons as $lesson) : ?>

                                <?php if ($lesson->status == 1) : ?>
                                    <li> <?= $lesson->teacher->data->user_nicename ?> / <?= $lesson->appointment ?></li>

                        <?php endif;
                            endforeach;
                        endif; ?>
                    </ul>

                    <ul class="recap m-5">
                        <!-- Teacher list -->
                        <h3>Vos professeurs</h3>
                        <?php if (in_array('student', $user->roles)) :
                            $lessons = $lessonModel->getLessonsByStudentId($userId); ?>
                            <?php foreach ($lessons as $lesson) : ?>
                                <?php if ($lesson->status == 1) : ?>
                                    <li class="userProfileLi"> <?= $lesson->teacher->data->user_nicename ?> / <?= $lesson->instrument->name ?> / <?= $lesson->teacher->data->user_email ?></li>

                            <?php endif;
                            endforeach; ?>
                        <?php endif; ?>

                    </ul>
                </div>
            </section>
    </div>
<?php endif;
?>
</div>

<!-- Display lesson into calendar-->
<div class="container">
    <section>
        <?php
        if (in_array('teacher', $user->roles)) :
            $lessons = $lessonModel->getLessonsByTeacherId($userId);
        else :
            $lessons = $lessonModel->getLessonsByStudentId($userId);
        endif;
        ?>
        <!-- Vuetify calendar -->
        <div id="calendar">
            <textarea id="lessons" style="display: none; width: 100%; height: 500px"><?= json_encode($lessons, JSON_PRETTY_PRINT); ?></textarea>
            <template>
                <v-app>
                    <v-sheet tile height="54" class="d-flex">
                        <v-btn icon class="ma-2" @click="$refs.calendar.prev()">
                            <v-icon>mdi-chevron-left</v-icon>
                        </v-btn>
                        <v-select v-model="type" :items="types" dense outlined hide-details class="ma-2" label="Calendrier"></v-select>
                        <v-spacer></v-spacer>
                        <v-btn icon class="ma-2" @click="$refs.calendar.next()">
                            <v-icon>mdi-chevron-right</v-icon>
                        </v-btn>
                    </v-sheet>
                    <v-sheet height="600">
                        <v-calendar :interval-format="intervalFormat" ref="calendar" v-model="value" :weekdays="weekday" :type="type" :events="events" :event-overlap-mode="mode" :event-overlap-threshold="30" :event-color="getEventColor" @change="getEvents" locale="fr"></v-calendar>
                    </v-sheet>
                </v-app>
            </template>
        </div>
    </section>
</div>

<section>
    <!-- Footer-->
    <?php get_template_part('partials/footer.tpl'); ?>
    <!-- wp footer -->
    <?php get_footer(); ?>
</section>

</body>

</html>