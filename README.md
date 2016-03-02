# Git Version Panel

Panel for Tracy debug panel.
Shows current branch and current revision hash to be able identify your deployed version on sight.

Inspired by https://gist.github.com/vvondra/3645108.

![](http://oi59.tinypic.com/34oviv9.jpg)

## Installing

Install library via composer:

```
composer require jandrabek/tracy-gitversion-panel
```


## Registerring...

### In older versions of Nette (< 2.2)

```
nette:
    debugger:
        bar:
            - JanDrabek\Tracy\GitVersionPanel
```

### In newer versions of Nette (>= 2.2)

```
tracy:
    bar:
        - JanDrabek\Tracy\GitVersionPanel
```

### In pure Tracy

```
Tracy\Debugger::getBar()->addPanel(new JanDrabek\Tracy\GitVersionPanel());
```