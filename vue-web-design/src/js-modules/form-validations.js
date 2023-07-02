export function validateInput(data, type) {
    if (type === 'email') {
        if (!data.match(/^[^ ]+@[^ ]+\.[a-z]{2,3}$/))
            return 'Invalid email address.'
    } else if (type === 'phone-number')
        if (!data.match(/[0-9]/))
            return "Should only contain digits."
        else if (data.length < 10)
            return "Phone number should be 10 digits long."
        else if (data.length > 10)
            return "Phone number should not be more than 10 digits."
}