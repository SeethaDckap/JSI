/**
 * Copyright © 2010-2021 Epicor Software Corporation: All Rights Reserved
 */

var config = {
    map: {
        '*': {
            passwordStrengthIndicator:   'Epicor_Common/epicor/common/js/password-strength-indicator',
            zxcvbn: 'Epicor_Common/epicor/common/js/zxcvbn'
        }
    },
    config: {
        mixins: {
            'mage/validation': {
                'Epicor_Common/epicor/common/js/validation-mixin': true
            }
        }
    }
};