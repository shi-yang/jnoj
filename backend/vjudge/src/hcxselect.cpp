/*
 * hcxselect - A CSS selector engine for htmlcxx
 * Copyright (C) 2011 Jonas Gehring
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the copyright holders nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */


#include <stack>
#include <sstream>
#include <vector>

#include "hcxselect.h"

extern "C" {
	#include "lexer.h"
}

//#define TRACE printf("%s: ", __FUNCTION__); printf
inline void TRACE(...) { }

#define ENSURE(c, s) \
	if (!(c)) { throw ParseException(l->pos, s); }


namespace hcxselect
{

// Anonymous namespace for local helpers
namespace
{

typedef htmlcxx::HTML::Node HTMLNode;

// Trims whitespace from the beginning and end of a string
std::string trim(const std::string &str)
{
	int start = 0;
	int end = str.length()-1;
	const char *data = str.c_str();

	while (start <= end && isspace(data[start])) ++start;
	while (end >= start && isspace(data[end])) --end;

	if (start > 0 || end < (int)str.length()) {
		return str.substr(start, (end - start + 1));
	}
	return str;
}

// Wrapper for strcasecmp
inline int strcasecmp(const std::string &s1, const std::string &s2)
{
	return ::strcasecmp(s1.c_str(), s2.c_str());
}

// Checks for string prefix
inline bool starts_with(const std::string &str, const std::string &start)
{
	return !::strncasecmp(str.c_str(), start.c_str(), start.length());
}

// Checks for string suffix
inline bool ends_with(const std::string &str, const std::string &end)
{
	if (str.length() >= end.length()) {
		return !::strcasecmp(str.c_str() + str.length() - end.length(), end.c_str());
	}
	return false;
}

// Delete all elements of a container
template<typename T>
inline void delete_all(const T &v)
{
	for (typename T::const_iterator it = v.begin(); it != v.end(); ++it) {
		delete *it;
	}
}

// String to number
template <class T> inline bool stoi(T *t, const std::string& s)
{
	std::istringstream iss(s);
	return !(iss >> *t).fail();
}

// Lexer for CSS selector grammar (wrapper for reentrant FLEX parser)
class Lexer
{
public:
	Lexer(const std::string &str)
		: pos(0), spos(0)
	{
		yylex_init(&yy);
		yy_scan_string(str.c_str(), yy);
	}

	~Lexer()
	{
		yylex_destroy(yy);
	}

	void unescape(std::string *str)
	{
		size_t pos = 0;
		while ((pos = str->find('\\', pos)) != std::string::npos) {
			if ((*str)[pos+1] == '\\') {
				str->replace(pos, 2, "");
			} else if (isdigit((*str)[pos+1])) {
				// Un-escape UTF-8 codes, sometimes followed by space
				size_t len = 1;
				while (isdigit((*str)[pos+len])) ++len;
				while (isspace((*str)[pos+len])) ++len;
				if (len > 1) {
					unsigned int x;
					std::stringstream ss;
					ss << std::hex << str->substr(pos+1, len-1);
					ss >> x;
					str->replace(pos, len, 1, x);
				}
			} else {
				str->replace(pos, 1, "");
			}
		}
	}

	inline int lex(std::string *text)
	{
		int token = yylex(yy);
		spos += yyget_leng(yy);
		if (token > 0) {
			*text = yyget_text(yy);
			pos = spos + 1 - text->length();
		} else {
			pos = spos;
		}
		if (token == IDENT) {
			unescape(text);
		}
		return token;
	}

	yyscan_t yy;
	int pos, spos;
};

namespace Selectors
{

// Abstract base class for selector functions
struct SelectorFn
{
	typedef tree<HTMLNode>::iterator NodeIt;

	virtual ~SelectorFn() { }
	virtual bool match(const NodeIt &it) const = 0;

protected:
	inline static bool hasParent(const NodeIt &it) {
		return (it.node->parent == NULL || strcasecmp(it->tagName(), "html"));
	}
};

// Universal selector (*)
struct Universal : SelectorFn
{
	bool match(const NodeIt &it) const
	{
		return it->isTag();
	}
};

// Type selector (E)
struct Type : SelectorFn
{
	Type(const std::string &type) : type(type) { }

	bool match(const NodeIt &it) const
	{
		return (it->isTag() && !strcasecmp(it->tagName(), type));
	}

	std::string type;
};

// Attribute selector (E[foo])
struct Attribute : SelectorFn
{
	Attribute(const std::string &attr) : attr(attr) { }

	bool match(const NodeIt &it) const
	{
		if (it->attributes().empty()) it->parseAttributes();
		return it->attribute(attr).first;
	}

	std::string attr;
};

// Attribute value, with optional comparison operator (E[foo=bar])
struct AttributeValue : SelectorFn
{
	AttributeValue(const std::string &attr, const std::string &value, char c = '=')
		: attr(attr), value(value), c(c) { }

	bool match(const NodeIt &it) const
	{
		if (value.empty() && c != '=') return false;

		if (it->attributes().empty()) it->parseAttributes();
		const std::string &str = it->attribute(attr).second;
		switch (c) {
			case '=': return !strcasecmp(str, value);
			case '^': return starts_with(str, value);
			case '$': return ends_with(str, value);
			case '*': return (str.find(value) != std::string::npos);
			case '|': return !(strcasecmp(str, value) && !starts_with(str, value + "-"));
			case '~': {
				// Split string by space and compare every part
				const char *ptr = str.c_str(), *last = str.c_str();
				const char *end = str.c_str() + str.length();
				int l = value.length();
				while (ptr < end) {
					while (*ptr && !isspace(*ptr)) ++ptr;
					if ((ptr - last) == l && !::strncasecmp(value.c_str(), last, l)) {
						return true;
					}
					while (*ptr && isspace(*ptr)) ++ptr;
					last = ptr;
				}
				return false;
			}
			default: break;
		}
		return true;
	}

	std::string attr;
	std::string value;
	char c;
};

// Pseudo class or element
struct Pseudo : SelectorFn
{
	Pseudo(const std::string &type, int an = 0, int b = 0)
		: type(type), an(an), b(b) { }

	bool checkNum(int i) const 
	{
		if (an == 0) {
			return (i == b);
		}
		return (((i - b) % an) == 0);
	}

	bool matchs(const NodeIt &it, const std::string &type) const
	{
		if (type == "root") {
			return !hasParent(it);
		} else if (type == "first-child") {
			if (!hasParent(it)) return false;
			NodeIt jt(it.node->parent->first_child);
			while (jt.node && !jt->isTag()) {
				jt = jt.node->next_sibling;
			}
			return (jt.node == it.node);
		} else if (type == "last-child") {
			if (!hasParent(it)) return false;
			NodeIt jt(it.node->parent->last_child);
			while (jt.node && !jt->isTag()) {
				jt = jt.node->prev_sibling;
			}
			return (jt.node == it.node);
		} else if (type == "first-of-type") {
			if (!hasParent(it)) return false;
			NodeIt jt(it.node->parent->first_child);
			while (jt.node && (!jt->isTag() || strcasecmp(jt->tagName(), it->tagName()))) {
				jt = jt.node->next_sibling;
			}
			return (jt.node == it.node);
		} else if (type == "last-of-type") {
			if (!hasParent(it)) return false;
			NodeIt jt(it.node->parent->last_child);
			while (jt.node && (!jt->isTag() || strcasecmp(jt->tagName(), it->tagName()))) {
				jt = jt.node->prev_sibling;
			}
			return (jt.node == it.node);
		} else if (type == "empty") {
			if (it->isTag()) {
				return (it.node->first_child == NULL ||
						(it.node->first_child->data.isComment() && it.node->first_child == it.node->last_child));
			}
			return (it->isComment() || it->length() == 0);
		} else if (type == "nth-child") {
			if (!hasParent(it)) return false;
			int i = 1;
			NodeIt jt(it.node->parent->first_child);
			while (jt.node && it.node != jt.node) {
				if (jt->isTag()) ++i;
				jt = jt.node->next_sibling;
			}
			return checkNum(i);
		} else if (type == "nth-last-child") {
			if (!hasParent(it)) return false;
			int i = 1;
			NodeIt jt(it.node->parent->last_child);
			while (jt.node && it.node != jt.node) {
				if (jt->isTag()) ++i;
				jt = jt.node->prev_sibling;
			}
			return checkNum(i);
		} else if (type == "nth-of-type") {
			if (!hasParent(it)) return false;
			int i = 1;
			NodeIt jt(it.node->parent->first_child);
			while (jt.node && it.node != jt.node) {
				if (jt->isTag() && !strcasecmp(jt->tagName(), it->tagName())) ++i;
				jt = jt.node->next_sibling;
			}
			return checkNum(i);
		} else if (type == "nth-last-of-type") {
			if (!hasParent(it)) return false;
			int i = 1;
			NodeIt jt(it.node->parent->last_child);
			while (jt.node && it.node != jt.node) {
				if (jt->isTag() && !strcasecmp(jt->tagName(), it->tagName())) ++i;
				jt = jt.node->prev_sibling;
			}
			return checkNum(i);
		} else if (type == "text") {
			return (!it->isTag() && !it->isComment());
		} else if (type == "comment") {
			return it->isComment();
		}
		return false;
	}

	bool match(const NodeIt &it) const
	{
		if (type == "only-child") {
			return matchs(it, "first-child") && matchs(it, "last-child");
		} else if (type == "only-of-type") {
			return matchs(it, "first-of-type") && matchs(it, "last-of-type");
		}
		return matchs(it, type);
	}

	std::string type;
	int an, b;
};

// Negation (:not)
struct Negation : SelectorFn
{
	Negation(SelectorFn *fn) : fn(fn) { }
	~Negation() { delete fn; }

	bool match(const NodeIt &it) const
	{
		return !fn->match(it);
	}

	SelectorFn *fn;
};

// A simple selector sequence
struct SimpleSequence : SelectorFn
{
	SimpleSequence(const std::vector<SelectorFn *> &fns) : fns(fns) { }
	~SimpleSequence() { delete_all(fns); }

	bool match(const NodeIt &it) const
	{
		std::vector<SelectorFn *>::const_iterator ft(fns.begin());
		std::vector<SelectorFn *>::const_iterator end(fns.end());
		while (ft != end && (*ft)->match(it)) {
			++ft;
		}
		return (ft == end);
	}

	std::vector<SelectorFn *> fns;
};

// Combinator ( , >, ~, +)
struct Combinator : SelectorFn
{
	Combinator(SelectorFn *left, SelectorFn *right, char c) : left(left), right(right), c(c) { }
	~Combinator() { delete left; delete right; }

	bool match(const NodeIt &it) const
	{
		// First, check if the node matches the right side of the combinator
		if (!right->match(it)) {
			return false;
		}

		// Check all suitable neighbor nodes using the left selector
		NodeIt jt;
		switch (c) {
			case ' ': // Descendant
			case '*': // Greatchild or further descendant
				if (!hasParent(it)) return false;
				jt = it.node->parent;
				if (c == '*' && jt.node) {
					jt = jt.node->parent;
				}
				while (jt.node) {
					if (left->match(jt)) {
						return true;
					}
					jt = jt.node->parent;
				}
				return false;

			case '>': // Child
				if (!hasParent(it)) return false;
				jt = it.node->parent;
				return jt.node && left->match(jt);

			case '+': // Adjacent sibling
				if (!hasParent(it)) return false;
				jt = it.node->prev_sibling;
				while (jt.node && !jt->isTag()) {
					jt = jt.node->prev_sibling;
				}
				return jt.node && left->match(jt);

			case '~': // General sibling
				if (!hasParent(it)) return false;
				jt = it.node->prev_sibling;
				while (jt.node) {
					if (jt->isTag() && left->match(jt)) {
						return true;
					}
					jt = jt.node->prev_sibling;
				}
				return false;

			default: break;
		}

		return false;
	}

	SelectorFn *left, *right;
	char c;
};

} // namespace Selectors

using Selectors::SelectorFn;

SelectorFn *parseSelector(Lexer *l, int &token, std::string &s);

// Tries to parse a simple selector
SelectorFn *parseSimpleSequence(Lexer *l, int &token, std::string &s)
{
	std::vector<SelectorFn *> fns;

	// [ type_selector | universal ]
	TRACE("%d: %s\n", token, s.c_str());
	if (token == IDENT) {
		fns.push_back(new Selectors::Type(s));
		token = l->lex(&s);
	} else if (token == '*') {
		fns.push_back(new Selectors::Universal());
		token = l->lex(&s);
	}

	// [ HASH | class | attrib | pseudo | negation ]*
	bool lex = true;
	while (token) {
		switch (token) {
		case HASH:
			fns.push_back(new Selectors::AttributeValue("id", s.substr(1)));
			break;
		case '.':
			token = l->lex(&s);
			ENSURE(token == IDENT, "Identifier expected");
			TRACE("%d: %s\n", token, s.c_str());
			fns.push_back(new Selectors::AttributeValue("class", s, '~'));
			break;
		case '[': {
			token = l->lex(&s);
			if (token == S) token = l->lex(&s);
			ENSURE(token == IDENT, "Identifier expected");
			std::string a = s;

			token = l->lex(&s);
			if (token == S) token = l->lex(&s);
			if (token == ']') {
				fns.push_back(new Selectors::Attribute(a));
				break;
			}

			int c = 0;
			switch (token) {
				case INCLUDES: c = '~'; break;
				case DASHMATCH: c = '|'; break;
				case PREFIXMATCH: c = '^'; break;
				case SUFFIXMATCH: c = '$'; break;
				case SUBSTRINGMATCH: c = '*'; break;
				case '=': c = '='; break;
				default: throw ParseException(l->pos, "Invalid character");
			}
			TRACE("got %d, %c\n", token, token);

			token = l->lex(&s);
			if (token == S) token = l->lex(&s);
			ENSURE(token == STRING || token == IDENT, "Token is neither string nor identifier"); 
			std::string v = (token == STRING ? s.substr(1, s.length()-2) : s);

			fns.push_back(new Selectors::AttributeValue(a, v, c));
			token = l->lex(&s);
			if (token == S) token = l->lex(&s);
			ENSURE(token == ']', "']' expected");
			break;
		}
		case ':': {
			token = l->lex(&s);
			if (token == ':') { token = l->lex(&s); s.insert(0, ":"); }
			if (token == IDENT) {
				fns.push_back(new Selectors::Pseudo(s));
			} else if (token == FUNCTION) {
				std::string f(s.substr(0, s.length()-1));
				int an = 0, b = 0;
				token = l->lex(&s);
				if (token == S) token = l->lex(&s);
				if (token == IDENT) {
					if (s == "odd" || s == "even") {
						an = 2; b = (s == "odd" ? 1 : 0);
						token = l->lex(&s);
						ENSURE(token == ')', "')' expected");
					} else if (s == "n" || s == "-n") {
						an = (s == "n" ? 1 : -1);
						token = l->lex(&s);
						if (token == PLUS) {
							token = l->lex(&s);
							if (token == S) token = l->lex(&s);
							ENSURE(token == NUMBER && stoi(&b, s), "Number expected");
							token = l->lex(&s);
							ENSURE(token == ')', "')' expected");
						} else if (token != ')') {
							throw ParseException(l->pos, "')' expected");
						}
					} else {
						throw ParseException(l->pos, "odd/even/n expected");
					}
				} else if (token == DIMENSION) {
					std::istringstream ss(s);
					ss >> an >> s;
					ENSURE(!ss.fail() && s == "n", "Expression of form 'an' expected");
					token = l->lex(&s);
					if (token == PLUS) {
						token = l->lex(&s);
						if (token == S) token = l->lex(&s);
						ENSURE(token == NUMBER && stoi(&b, s), "Number expected");
						token = l->lex(&s);
						ENSURE(token == ')', "')' expected");
					}
				} else if (token == NUMBER) {
					ENSURE(stoi(&b, s), "Number expected");
					token = l->lex(&s);
					ENSURE(token == ')', "')' expected");
				} else {
					throw ParseException(l->pos, "Invalid expression");
				}
				TRACE("pseudo %s,with %dn + %d\n", f.c_str(), an, b);
				fns.push_back(new Selectors::Pseudo(f, an, b));
			} else {
				throw ParseException(l->pos, "Identifier or funtion expected");
			}
			break;
		}
		case NOT: {
			token = l->lex(&s);
			fns.push_back(new Selectors::Negation(parseSelector(l, token, s)));
			ENSURE(token == ')', "')' expected");
			break;
		}
		case ')': // For negations
		default: goto finish;
		}

		if (lex) {
			token = l->lex(&s);
		}
		lex = true;
	}

finish:
	return new Selectors::SimpleSequence(fns);
}

// Recursive parsing function
SelectorFn *parseSelector(Lexer *l, int &token, std::string &s)
{
	if (token == S) token = l->lex(&s);
	SelectorFn *fn = parseSimpleSequence(l, token, s);

	while (token) {
		TRACE("%d: %s\n", token, s.c_str());
		bool space = false;
		if (token == S) {
			space = true;
			token = l->lex(&s);
		}
		TRACE("%d: %s\n", token, s.c_str());

		char c = -1;
		switch (token) {
			case S: c = ' '; break;
			case PLUS: c = '+'; break;
			case GREATER: c = '>'; break;
			case TILDE: c = '~'; break;
			case '*': c = '*'; break;

			case 0: return fn;
			default:
				if (space) {
					c = ' ';
				} else {
					return fn;
				}
				break;
		}

		if (c != ' ') {
			token = l->lex(&s);
			TRACE("%d: %s\n", token, s.c_str());
			if (token == S) token = l->lex(&s);
		}
		TRACE("%d: %s\n", token, s.c_str());
		SelectorFn *fn2 = parseSimpleSequence(l, token, s);
		fn = new Selectors::Combinator(fn, fn2, c);
	}

	return fn;
}

// Parses a CSS selector expression and returns a set of functions
std::vector<SelectorFn *> parse(const std::string &expr)
{
	std::vector<SelectorFn *> fns;
	int token;
	std::string s;

	Lexer l(trim(expr));
	while ((token = l.lex(&s))) {
		fns.push_back(parseSelector(&l, token, s));
		if (token != COMMA && token != 0) {
			throw ParseException(l.pos, "Comma expected");
		}
	}

	return fns;
}

// Matches a set of nodes against a selector
NodeSet match(const NodeSet &nodes, const SelectorFn *fn)
{
	std::stack<tree<HTMLNode>::iterator> stack;
	for (NodeSet::const_iterator it(nodes.begin()); it != nodes.end(); ++it) {
		stack.push(tree<HTMLNode>::iterator(*it));
	}

	// Depth-first traversal using a stack
	NodeSet result;
	while (!stack.empty()) {
		tree<HTMLNode>::iterator it = stack.top();
		stack.pop();

		// Check if selector matches
		if (fn->match(it)) {
			result.insert(it.node);
		}

		// Inspect all child nodes of non-matching elements
		tree<HTMLNode>::sibling_iterator jt;
		for (jt = it.begin(); jt != it.end(); ++jt) {
			stack.push(jt);
		}
	}

	return result;
}

} // Anonymous namespace


/*!
 * Checks if node \p a < node \p b by comparing their positions in the document.
 */
bool NodeComp::operator()(Node *a, Node *b) const {
	if (a == NULL || b == NULL) {
		return a < b;
	}
	return a->data.offset() < b->data.offset();
}

// Applies a CSS selector expression to a document tree.
NodeSet select(const tree<HTMLNode> &tree, const std::string &expr)
{
	// Select the <html> node from the tree and use it as the root node
	NodeSet v;
	::tree<HTMLNode>::iterator it;
	for (it = tree.begin(); it != tree.end(); ++it) {
		if (!strcasecmp(it->tagName(), "html")) {
			v.insert(it.node);
			break;
		}
	}

	if (expr.empty()) {
		return v;
	}
	return select(v, expr);
}

// Applies a CSS selector expression to a set of nodes.
NodeSet select(const NodeSet &nodes, const std::string &expr)
{
	// Parse expression
	std::vector<SelectorFn *> fns = parse(expr);

	NodeSet result;
	std::vector<SelectorFn *>::const_iterator it;
	for (it = fns.begin(); it != fns.end(); ++it) {
		NodeSet v = match(nodes, *it);
		result.insert(v.begin(), v.end());
	}

	delete_all(fns);
	return result;
}


/*!
 * Constructs an empty selection.
 */
Selection::Selection()
{
}

/*!
 * Constructs a selection containing a whole tree and optionally
 * applies a selector.
 */
Selection::Selection(const tree<htmlcxx::HTML::Node> &tree, const std::string &expr)
{
	NodeSet v = hcxselect::select(tree, expr);
	insert(v.begin(), v.end());
}

/*!
 * Constructs a selection from a set of nodes and optionally 
 * applies a selector.
 */
Selection::Selection(const NodeSet &nodes, const std::string &expr)
{
	if (!expr.empty()) {
		NodeSet v = hcxselect::select(nodes, expr);
		insert(v.begin(), v.end());
	} else {
		insert(nodes.begin(), nodes.end());
	}
}

/*!
 * Returns a new selection by selecting elements from this 
 * selection using the given selector expression.
 */
Selection Selection::select(const std::string &expr)
{
	return hcxselect::select(*this, expr);
}

} // namespace hcxselect
