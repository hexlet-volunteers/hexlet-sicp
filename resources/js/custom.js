import $ from 'jquery'
import tabNames from './common/tabNamesMap.js'

// Этими хешами владеет React-корень упражнения, Bootstrap их не трогает
const escapeFragments = Object.values(tabNames)
const url = window.location.href
if (url.indexOf('#') > 0) {
  const activeTabUrl = url.substring(url.indexOf('#') + 1)
  if (!escapeFragments.includes(activeTabUrl)) {
    const selector = activeTabUrl.startsWith('comment')
      ? '.nav[role="tablist"] a[href="#exercise-discussion"]'
      : `.nav[role="tablist"] a[href="#${activeTabUrl}"]`
    const activeTab = document.querySelector(selector)
    if (activeTab) {
      const tab = new window.bootstrap.Tab(activeTab)
      tab.show()
    }
  }
}

$('a[role="tab"]').on('click', function () {
  const hash = $(this).attr('href')
  const newUrl = url.split('#')[0] + hash
  history.replaceState(null, null, newUrl)
})
