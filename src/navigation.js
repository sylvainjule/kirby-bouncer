export function showBar(user) {
    return Boolean(user?.nav && Array.isArray(user.allowed) && user.allowed.length > 1);
}

export function visiblePages(user, parent) {
    return showBar(user) ? user.allowed.filter(page => page.path !== parent) : [];
}

export function mountHost(anchor) {
    const panel = anchor?.closest('.k-panel');
    const main  = panel?.querySelector('.k-panel-main');
    
    if(!main?.parentElement) return null;

    const host = anchor.ownerDocument.createElement('div');
          host.className = 'bouncer-nav-host';
    
    main.parentElement.insertBefore(host, main);
    return host;
}
