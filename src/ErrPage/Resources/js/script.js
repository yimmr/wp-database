function imExceptionPageTabs(tabs) {
    var tabs = document.querySelectorAll(tabs)
    for (var i = 0; i < tabs.length; i++) {
        var tabItems = tabs[i].querySelectorAll('.tab-nav > ul > li')
        var contents = tabs[i].querySelectorAll('.tab-main > div')
        for (var t = 0; t < tabItems.length; t++) {
            tabItems[t].addEventListener('click', function (e) {
                var event = e || window.event
                var target = event.target || event.srcElement
                var tabID = this.getAttribute('data-tab')
                if (target.nodeName.toLocaleLowerCase() == 'li') {
                    for (var d = 0; d < tabItems.length; d++) {
                        tabItems[d].className = tabItems[d].className.replace('active', '')
                    }
                    target.className += 'active'

                    imExceptionPageTabsShow(contents, tabID)
                }
            })
        }
    }
}

function imExceptionPageTabsShow(contents, id) {
    for (var i = 0; i < contents.length; i++) {
        if (contents[i].getAttribute('data-tab') == id) {
            contents[i].style.display = 'block'
        } else {
            contents[i].style.display = 'none'
        }
    }
}
