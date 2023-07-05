export function validateInput(data, type) {
    if (type === 'email') {
        if (!data.match(/^[^ ]+@[^ ]+\.[a-z]{2,3}$/))
            return 'Invalid email address.'
    } else if (type === 'phone-number') {
        if (!data.match(/[0-9]/))
            return "Should only contain digits."
        else if (data.length < 10)
            return "Should be 10 digits long."
        else if (data.length > 10)
            return "Should not be more than 10 digits."
    } else if (type === 'price') {
        if (!data.match(/[0-9]/)) {
            return 'Must be a valid number.'
        } else if (data < 0)
            return 'Must be greater than 0.'
    } else if (type === 'username') {
        if (data.length < 6 || data.length > 30)
            return 'Must be between 6 - 30 characters in length.'
        else if (!data.match(/^[A-Za-z][A-Za-z0-9_]{5,29}$/))
            return 'Should only contain english alphabetic characters, numbers and underscore (_).'
    } else if (type === 'password') {
        if (data.length < 8 || data.length > 24)
            return 'Should be between 8 - 24 characters in length.';
    }
}