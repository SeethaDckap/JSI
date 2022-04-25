/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

Raphael.fn.drawGrid = function (x, y, w, h, wv, hv, color) {
    color = color || "#000";
    var path = ["M", Math.round(x) + .5, Math.round(y) + .5, "L", Math.round(x + w) + .5, Math.round(y) + .5, Math.round(x + w) + .5, Math.round(y + h) + .5, Math.round(x) + .5, Math.round(y + h) + .5, Math.round(x) + .5, Math.round(y) + .5],
        rowHeight = h / hv,
        columnWidth = w / wv;
    for (var i = 1; i < hv; i++) {
        path = path.concat(["M", Math.round(x) + .5, Math.round(y + i * rowHeight) + .5, "H", Math.round(x + w) + .5]);
    }
    /*for (i = 1; i < wv; i++) {
     path = path.concat(["M", Math.round(x + i * columnWidth) + .5, Math.round(y) + .5, "V", Math.round(y + h) + .5]);
     }*/
    return this.path(path.join(",")).attr({stroke: color});
};

function getAnchors(p1x, p1y, p2x, p2y, p3x, p3y) {
    var l1 = (p2x - p1x) / 2,
        l2 = (p3x - p2x) / 2,
        a = Math.atan((p2x - p1x) / Math.abs(p2y - p1y)),
        b = Math.atan((p3x - p2x) / Math.abs(p2y - p3y));
    a = p1y < p2y ? Math.PI - a : a;
    b = p3y < p2y ? Math.PI - b : b;
    var alpha = Math.PI / 2 - ((a + b) % (Math.PI * 2)) / 2,
        dx1 = l1 * Math.sin(alpha + a),
        dy1 = l1 * Math.cos(alpha + a),
        dx2 = l2 * Math.sin(alpha + b),
        dy2 = l2 * Math.cos(alpha + b);
    return {
        x1: p2x - dx1,
        y1: p2y + dy1,
        x2: p2x + dx2,
        y2: p2y + dy2
    };
}

function drawChart(table_id) {
    // Grab the data
    var labels = [],
        labels_data = {},
        data = [];
    jQuery("#" + table_id + " tfoot th").each(function () {
        labels.push(jQuery(this).html());
    });
    jQuery("#" + table_id + " tbody tr").each(function (i) {
        data[i] = [];
        labels_data[i] = [];
        jQuery(this).find('td').each(function(){
            labels_data[i].push(jQuery(this).find('.chart_label').html());
            data[i].push(jQuery(this).find('.chart_data').html());
        });
    });

    jQuery("#" + table_id).hide();

    // Define options and create object drawable
    var width = 800, height = 500, leftgutter = 40, bottomgutter = 40, topgutter = 40;
    var $raphael = Raphael("holder", width, height);
    var max = Math.max.apply(Math, data.map(function(i) { return Math.max.apply(Math, i); }));
    var Y = (height - bottomgutter - topgutter) / max, label = $raphael.set(), X = (width - leftgutter) / labels.length;
    label.push($raphael.text(60, 12, "title").attr({font: '12px bold', fill: "#fff"}));
    //label.push($raphael.text(60, 35, "desc").attr({font: '10px', fill: "#ccc"}));
    label.hide();
    var frame = $raphael.popup(100, 100, label, "right").attr({fill: "#000", stroke: "#666", "stroke-width": 2, "fill-opacity": .7}).hide();

    // Grid from the back
    $raphael.drawGrid(leftgutter + X * .5 + .5, topgutter + .5, width - leftgutter - X, height - topgutter - bottomgutter, 10, 10, "#ccc");

    var is_label_visible = false, leave_timer, max_x_labels_displayed = 7;
    // Draw lines, dots and path
    for (var j = 0; j < data.length; j++) {
        var color_random = "hsl(" + [Math.random(), .5, .5] + ")",
            path = $raphael.path().attr({stroke: color_random, "stroke-width": 2, "stroke-linejoin": "bevel"}),
            lx = 0, ly = 0;

        var step;
        for (var i = 0, labels_length = labels.length; i < labels_length; i++) {
            var y = Math.round(height - bottomgutter - Y * data[j][i]),
                x = Math.round(leftgutter + X * (i + .5));
            if((labels_length > max_x_labels_displayed && ((i % Math.ceil(labels_length / max_x_labels_displayed)) == 0 || i == labels_length - 1)) || labels_length <= max_x_labels_displayed){
                var t = $raphael.text(x, height - 10, labels[i]);
            }

            if (!i) {
                step = ["M", x, y, "L", x, y];
            }
            if (i && i < labels_length - 1) {
                step = step.concat([x, y]);
            }
            var dot = $raphael.circle(x, y, 4).attr({fill: color_random, stroke: color_random, "stroke-width": 0});
            (function (x, y, data, label_data, label_axis) {
                dot.hover(
                    function(){
                        var side = "right";
                        this.attr('stroke-width', 6);
                        label[0].attr({ text: label_data });
                        //label[1].attr({ text: label_data });
                        if (x + frame.getBBox().width > width) {
                            side = "left";
                        }
                        var popup = $raphael.popup(x, y, label, side, 1);
                        var anim = Raphael.animation({ path: popup.path, transform: ["t", popup.dx, popup.dy] }, 200);
                        lx = label[0].transform()[0][1] + popup.dx;
                        ly = label[0].transform()[0][2] + popup.dy;
                        frame.show().stop().animate(anim);
                        label[0].show().stop().animateWith(frame, anim, {transform: ["t", lx, ly]}, 200);
                        //label[1].show().stop().animateWith(frame, anim, {transform: ["t", lx, ly]}, 200);
                    }, function(){
                        this.attr('stroke-width', 0);
                    });
            })(x, y, data[j][i],     labels_data[j][i], labels[i]);

        }
        step = step.concat([x, y, x, y]);
        path.attr({path: step});
    }
    frame.toFront();
    label[0].toFront();
}