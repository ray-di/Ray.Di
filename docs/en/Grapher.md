## Graphing Ray.Di Applications

When you've written a sophisticated application, Ray.Di rich introspection API can describe the object graph in detail. The object-visual-grapher exposes this data as an easily understandable visualization. It can show the bindings and dependencies from several classes in a complex application in a unified diagram.

### Generating a .dot file
Ray.Di's grapher leans heavily on [GraphViz](http://www.graphviz.org/), an open source graph visualization package. It cleanly separates graph specification from visualization and layout. To produce a graph `.dot` file for an `Injector`, you can use the following code:

```php
use Ray\ObjectGrapher\ObjectGrapher;

$dot = (new ObjectGrapher)(new FooModule);
file_put_contents('path/to/graph.dot', $dot);
```

### The .dot file
Executing the code above produces a `.dot` file that specifies a graph. Each entry in the file represents either a node or an edge in the graph. Here's a sample `.dot` file:

```dot
digraph injector {
graph [rankdir=TB];
dependency_BEAR_Resource_ResourceInterface_ [style=dashed, margin=0.02, label=<<table cellspacing="0" cellpadding="5" cellborder="0" border="0"><tr><td align="left" port="header" bgcolor="#ffffff"><font color="#000000">BEAR\\Resource\\ResourceInterface<br align="left"/></font></td></tr></table>>, shape=box]
dependency_BEAR_Resource_FactoryInterface_ [style=dashed, margin=0.02, label=<<table cellspacing="0" cellpadding="5" cellborder="0" border="0"><tr><td align="left" port="header" bgcolor="#ffffff"><font color="#000000">BEAR\\Resource\\FactoryInterface<br align="left"/></font></td></tr></table>>, shape=box]
dependency_BEAR_Resource_ResourceInterface_ -> class_BEAR_Resource_Resource [style=dashed, arrowtail=none, arrowhead=onormal]
dependency_BEAR_Resource_FactoryInterface_ -> class_BEAR_Resource_Factory [style=dashed, arrowtail=none, arrowhead=onormal]
```

### Rendering the .dot file
 You can then paste that code into [GraphvizOnline](https://dreampuf.github.io/GraphvizOnline/)to render it. 

On Linux, you can use the command-line `dot` tool to convert `.dot` files into images.
```shell
dot -T png graph.dot > graph.png
```

![graph](https://user-images.githubusercontent.com/529021/72650686-866ec100-39c4-11ea-8b49-2d86d991dc6d.png)


#### Graph display

Edges:
   * **Solid edges** represent dependencies from implementations to the types they depend on.
   * **Dashed edges** represent bindings from types to their implementations.
   * **Double arrows** indicate that the binding or dependency is to a `Provider`.

Nodes:
   * Implementation types are given *black backgrounds*.
   * Implementation instances have *gray backgrounds*.
