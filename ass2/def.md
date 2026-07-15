\- - 

Concepts: The basic structure of PL, syntax, semantics, data types, control 

structures, …etc. 

Paradigms: the model, an approach or the way of reasoning to solve the problem.







Programming Languages Views 

There are 3 different views to consider a PL: 

1- Designer: (The inventor of the language) 

2- Implementer: (The one who build the compiler or the interpreter) 

3- User. (The one writes programs in the language)





A Programming language is a notational system for describing 

COMPUTATION in MACHINE READABLE and HUMAN READABLE 

form.





(1) Imperative or Procedural Paradigm =>Pascal and C.

Language that is based on this model is characterized by: 

1\. Sequential Execution of Instructions. 

2\. Using Variables to Represent Memory Locations. 

3\. Using Assignment Statements to Change the Value of a Variable.





(2)  Functional Paradigm => LISP

1\. There is NO Notion of Variables or Assignment Statements in this Paradigm. 

&#x20;

2\. Repetition is not Expressed in Loops, but is Achieved by Recursive Calls.







(3)  Logical Paradigm : PROLOG



(4) Object Oriented Paradigm : java 

Encapsulation of Data and Functions. 

Inheritance. 

Polymorphism.





**ch3**



Translator is :  an Algorithm Which Translates the Source Code Into a Target Code. 





Lexical Analysis(scanner) : Which simply groups the characters of the source 

code to form what is called the Tokens. This Only detects legal character 

errors. 





Syntax Analysis (Parser) : Groups the set of tokens from the scanner to form 

Syntax Structures. 





Semantic Analysis: Gives the syntax structures meaning. This is the hardest 

task. 



Code Generation : Both Compilers and Interpreters do code generation, but 

they differ in how. While the Compiler generates Object Code, the interpreter generates Intermediate Code. 







There are 3 types of Runtime Environment : 

Fully-Static Environment. 

Fully-Dynamic Environment. 

Stack-Based Environment. 





Error Detection and recovery 

1\. 

Lexical Errors 

2 semantic 

3 Syntax

4 logical 







Programming Languages Domain 



1.Scientific Domain : FORTRAN, C and ALGOL60

2.Business Domain : COBOL and JAVA 
3.Artificial Intelligence Domain :  LISP and PROLOG 

4.Systems Programming :  Assembly Language, and C. 

5.Very High Level Languages :python and bash.  

&#x20;

These tokens are divided into 3 kinds:



(1) : NAMES =>  (a) Keywords/Reserved: which are words such as if/else/while. These names

&#x09;	can’t be used as variable names. They have a specific place and function

&#x09;	(b) User Defined Names: which are the names declared by the user.

&#x09;	(c) Standard Identifiers: such as sin, cos, and sqrt. Similar to reserved words,

&#x09;	these standard library identifiers cannot be used as variable names and are not

&#x09;	included in production rules





(2) :  Values: such as integers(1, 2, 3, 4) or floating point(1.1, 2.34, 5234.123) etc.





(3)  Special Symbols/Tokens: these are the logical(==, \&\&, ||) and arithmetic op

erations(+,-, \*, /), parenthesis(\[], {}, ()), or any other tokens that are not from

the first or second kind

















&#x20;







