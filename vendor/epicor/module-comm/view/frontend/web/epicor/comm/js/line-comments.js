/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

Review.prototype.save = Review.prototype.save.wrap(function (orig) {
    $('line_comment_holder').update('');
    $$('.line_comment').each(function (el) {
        newEl = el.clone();
        newEl.value = el.value
        $('line_comment_holder').insert({bottom: newEl});
    });
    orig();
});