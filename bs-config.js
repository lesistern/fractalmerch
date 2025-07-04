module.exports = {
    proxy: "192.168.0.145/proyecto",
    host: "0.0.0.0",
    port: 3000,
    files: [
        "**/*.php",
        "**/*.css", 
        "**/*.js",
        "**/*.html"
    ],
    ignore: [
        "node_modules",
        "*.map"
    ],
    reloadDelay: 300,
    injectChanges: true,
    notify: {
        styles: [
            "display: none; ",
            "padding: 15px;",
            "font-family: sans-serif;",
            "position: fixed;",
            "font-size: 0.9em;",
            "z-index: 9999;",
            "bottom: 0px;",
            "right: 0px;",
            "border-bottom-left-radius: 5px;",
            "background-color: #1B2032;",
            "opacity: 0.4;",
            "margin: 0;",
            "color: white;",
            "text-align: center"
        ]
    },
    open: false,
    logLevel: "info",
    logPrefix: "🚀 Proyecto LAN",
    browser: "default",
    cors: true,
    xip: false,
    hostnameSuffix: false,
    reloadOnRestart: true,
    ghostMode: {
        clicks: true,
        forms: true,
        scroll: true
    },
    watchEvents: [
        'add',
        'change',
        'unlink',
        'addDir',
        'unlinkDir'
    ]
};
