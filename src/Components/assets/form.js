var ImwpImageField = {
    upload: function (el) {
        if (typeof el.media === 'undefined') {
            el.media = wp.media({ library: { type: 'image' }, multiple: false })
            el.media.on('select', function () {
                var image = el.media.state().get('selection').first().attributes
                var input = el.parentNode.querySelector('input')
                var img = el.parentNode.querySelector('img')

                if (!img) {
                    img = document.createElement('img')
                    el.parentNode.insertBefore(img, el.parentNode.firstChild)
                }
                img.setAttribute('src', image.url)

                if (input) input.setAttribute('value', image.id)
            })
        }
        el.media.open()
    },
    cancel: function (el) {
        var input = el.parentNode.querySelector('input')
        var img = el.parentNode.querySelector('img')
        if (img) el.parentNode.removeChild(img)
        if (input) input.setAttribute('value', '')
    },
}
