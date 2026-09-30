<?php get_header(); ?>

<main class="container main">
    <section class="feed">
        <h1 class="feed__title">Статьи</h1>

        <div class="cards">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <?php
                    $post_id = get_the_ID();
                    $counts = likes_get_counts($post_id);
                    $user_vote = likes_get_user_vote($post_id, likes_client_ip());
                    $category = get_the_category();
                    ?>
                    <article class="card">
                        <a class="card__thumb" href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('medium_large'); ?>
                            <?php else : ?>
                                <span class="card__thumb-empty"></span>
                            <?php endif; ?>
                        </a>

                        <div class="card__body">
                            <?php if (!empty($category)) : ?>
                                <a class="card__cat" href="<?php echo esc_url(get_category_link($category[0]->term_id)); ?>">
                                    <?php echo esc_html($category[0]->name); ?>
                                </a>
                            <?php endif; ?>

                            <h2 class="card__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>

                            <p class="card__excerpt"><?php echo esc_html(likes_excerpt(28)); ?></p>

                            <div class="card__meta">
                                <time class="card__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                    <?php echo esc_html(get_the_date()); ?>
                                </time>

                                <div class="likes" data-post="<?php echo esc_attr($post_id); ?>">
                                    <button type="button" class="likes__btn likes__btn--up <?php echo $user_vote === 1 ? 'is-active' : ''; ?>" data-vote="1">
                                        <span class="likes__icon">▲</span>
                                        <span class="likes__count" data-count="up"><?php echo esc_html($counts['up']); ?></span>
                                    </button>
                                    <button type="button" class="likes__btn likes__btn--down <?php echo $user_vote === -1 ? 'is-active' : ''; ?>" data-vote="-1">
                                        <span class="likes__icon">▼</span>
                                        <span class="likes__count" data-count="down"><?php echo esc_html($counts['down']); ?></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else : ?>
                <p>Пока нет статей.</p>
            <?php endif; ?>
        </div>

        <div class="feed__pagination">
            <?php the_posts_pagination(['mid_size' => 1]); ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
