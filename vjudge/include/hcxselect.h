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


#ifndef HCXSELECT_H_
#define HCXSELECT_H_

#include <exception>
#include <string>
#include <set>

#include <htmlcxx/html/Node.h>
#include <htmlcxx/html/tree.h>

#define HCXSELECT_VERSION_STR "1.1"
#define HCXSELECT_VERSION_MAJOR 1
#define HCXSELECT_VERSION_MINOR 1
#define HCXSELECT_VERSION_RELEASE 0


/*!
 * Library namespace
 */
namespace hcxselect {

  /*!
   * \class hcxselect::Node
   * Conenient typedef for \p tree_node_<htmlcxx::HTML::Node>.
   */
  typedef tree_node_<htmlcxx::HTML::Node> Node;

  /*!
   * Comparison class for nodes.
   */
  struct NodeComp {
    bool operator()(Node *a, Node *b) const;
  };

  /*!
   * \class hcxselect::NodeSet
   * Custom type for a set of nodes.
   * This is actually a typedef for a \p std::set with the correct item and
   * comparison templates.
   */
  typedef std::set<Node *, NodeComp> NodeSet;


  /*!
   * Applies a CSS selector expression to a whole HTML tree.
   * \note May throw a ParseException.
   *
   * \param tree The HTML tree
   * \param expr The CSS selector expression
   * \returns A set of nodes that matches the given selector
   */
  NodeSet select(const tree<htmlcxx::HTML::Node> &tree, const std::string &expr);

  /*!
   * Applies a CSS selector expression to a set of nodes.
   * \note May throw a ParseException.
   *
   * \param nodes The set of nodes
   * \param expr The CSS selector expression
   * \returns A set of nodes that matches the given selector
   */
  NodeSet select(const NodeSet &nodes, const std::string &expr);

  /*!
   * Convenient wrapper class for select().
   * This is a subclass of NodeSet, providing convenient constructors
   * and a select() member function. This allows for chaining of different
   * selectors.
   */
  class Selection : public NodeSet {
  public:
    Selection();
    Selection(const tree<htmlcxx::HTML::Node> &tree, const std::string &expr = std::string());
    Selection(const NodeSet &nodes, const std::string &expr = std::string());

    Selection select(const std::string &expr);
  };

  typedef Selection Selector;

  /*!
   * Exception that may be thrown when parsing a selector expression.
   */
  class ParseException : std::exception {
  public:

    /*!
     * Constructor.
     */
    ParseException(int pos, const char *info = NULL)
    : m_pos(pos), m_info(info) {
    }

    /*!
     * Returns the error string.
     */
    const char *what() const throw () {
      return m_info;
    }

    /*!
     * Returns the parser position inside the current selector
     */
    int position() const throw () {
      return m_pos;
    }

  private:
    int m_pos;
    const char *m_info;
  };

} // namespace hcxselect

#endif // HCXSELECT_H_
