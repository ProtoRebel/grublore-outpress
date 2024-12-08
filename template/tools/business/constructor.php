<?php if(!defined('WPINC')) { die; }

/*  BUSINESS TEMPLATE TAG CONSTRUCTOR
	----------------------------- */

// Template tag
function op_business($data, $options = array()) {
    $fields = array(
        'name'      => 'business_name',
        'tagline'   => 'business_tagline',
        'color'     => 'business_color',
        'logo'      => 'business_logo',
        'phone'     => 'business_phone',
        'email'     => 'business_email',
        'address'   => 'business_address',
        'social'    => 'business_social'
    );
    $fetch = get_field($fields[$data], 'options');
    $output = is_string($fetch) ? $fetch : array();

    switch($data) {
        case 'name':
            if(empty($fetch)) {
                $output = get_bloginfo('name');
            } else {
                if(empty($options)) {
                    $output = $fetch['default'];
                } else {
                    $output = $fetch[$options[0]];
                }
            }
            break;
        case 'tagline':
            if(empty($options)) {
                $output = $fetch['motto'];
            } else {
                foreach($options as $o) {
                    $output = $fetch[$o];
                }
            }
            break;
        case 'logo':
            if(empty($options)) {
                $output = wp_get_attachment_image($fetch['primary'], 'large', false, ['class' => 'business logo logo-primary']);
            } else {
                foreach($options as $o) {
                    $output = wp_get_attachment_image($fetch[$o], 'large', false, ['class' => 'business logo logo-' . $o]);
                }
            }
            break;
        case 'phone':
        case 'email':
            $all = $fetch;
            $allArray = array();
            if(!in_array('all', $options)) {
                foreach($all as $a => $e) {
                    if($e['featured'] === false) {
                        unset($all[$a]);
                    }
                }
            }
            if(!empty($all)) {
                $all = array_values($all);
                foreach($all as $a) {
                    $protocol = 'mailto:';
                    $link = $a['email_address'];
                    $value = $link;
                    $labelSep = ': ';
                    $label = in_array('label', $options) ? '<strong>' . (empty($a['name']) ? 'Email' : $a['name']) . $labelSep . '</strong>' : '';
                    $aria_label = sprintf('Email us at %s', $link);
                    if($data === 'phone') {
                        $protocol = 'tel:';
                        $link = $a['number'];
                        $value = empty($a['phoneword']) ? util_phone($link) : $a['phoneword'];
                        $label = in_array('label', $options) ? '<strong>' . (empty($a['name']) ? 'Phone' : $a['name']) . $labelSep . '</strong>' : '';
                        $aria_label = sprintf('Call us at %s', $link);
                    }
                    $text = $label . $value;
                    $singleVal = sprintf('<a href="%s%s" target="_blank" aria-label="%s" class="business %s">%s</a>', $protocol, $link, $aria_label, $data, $text);
                    $allArray[] = $singleVal;
                }
            }
            if(in_array('list', $options)) {
                $outputWrapped = array();
                foreach($allArray as $a) {
                    $outputWrapped[] = sprintf('<li>%s</li>', $a);
                }
                $output = sprintf('<ul class="business list-%s">%s</ul>', $data, implode('', $outputWrapped));
            } else {
                $output = implode(', ', $allArray);
            }

            break;
        case 'address':
            $inline = in_array('inline', $options);
            if($inline) {
                foreach($options as $o=>$e) {
                    if($e === 'inline') {
                        unset($options[$o]);
                    }
                }
            }
            $options = array_values($options);
            foreach($options as $o) {
                $address = $fetch[$o . '_address'];
                $addressDefault = sprintf('<address class="business address">%s</address>', $address);
                $addressInline = sprintf('<span class="business address">%s</span>', str_replace('<br />', ', ', $address));
                $output = $inline ? $addressInline : $address;
            }
            break;
        case 'social':
            $socialArray = array();
            if(in_array('embeds', $options)) {
                $fetch = get_field('business_social_embeds', 'options');
                foreach($fetch as $s) {
                    array_push($socialArray, sprintf('<div id="%s" class="embed">%s</div>', util_stringLint($s['key']), $s['code']));
                }
            } elseif(in_array('accounts', $options)) {
                $fetch = get_field('business_social_accounts', 'options');
                foreach($fetch as $s) {
                    $url = $s['link'];
                    $title = sprintf('Visit us on %s', $s['name']);
                    // ToDo: Fix bug with SVG pulling in as SVG instead of IMG element
                    $icon = $s['icon']['mime_type'] === 'image/svg+xml' ? get_template_part('//localhost:4000/wp-content/uploads/2023/11/emblem-outpress.svg') : wp_get_attachment_image($s['icon']['ID'], 'small');
                    $iconDisplay = empty($s['icon']) ? $s['name'] : $icon;
                    array_push($socialArray, sprintf('<a href="%s" title="%s" target="_blank" class="business social-account">%s</a>', $url, $title, $iconDisplay));
                }
                $outputWrap = array();
                foreach($socialArray as $social) {
                    $outputWrap[] = sprintf('<li>%s</li>', $social);
                }
                $output = sprintf('<ul class="business social">%s</ul>', implode('', $outputWrap));
            }
            break;
    }
    return $output;
}