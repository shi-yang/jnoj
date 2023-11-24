// @ts-check
// Note: type annotations allow type checking and IDEs autocompletion

const  path = require('path');
/**
 * @type {import('redocusaurus').PresetEntry}
 */
const redocusaurus = [
  'redocusaurus',
  {
    debug: Boolean(process.env.DEBUG || process.env.CI),
    config: path.join(__dirname, 'redocly.yaml'),
    specs: [
      {
        id: 'user',
        spec: '../api/interface/v1/user.swagger.json',
        route: '/api/interface/v1/user',
      },
      {
        id: 'group',
        spec: '../api/interface/v1/group.swagger.json',
        route: '/api/interface/v1/group',
      },
      {
        id: 'contest',
        spec: '../api/interface/v1/contest.swagger.json',
        route: '/api/interface/v1/contest',
      },
      {
        id: 'problem',
        spec: '../api/interface/v1/problem.swagger.json',
        route: '/api/interface/v1/problem',
      },
      {
        id: 'submission',
        spec: '../api/interface/v1/submission.swagger.json',
        route: '/api/interface/v1/submission',
      },
      {
        id: 'sandbox',
        spec: '../api/interface/v1/sandboxs.swagger.json',
        route: '/api/interface/v1/sandboxs',
      },
    ],
    theme: {
      /**
       * Highlight color for docs
       */
      primaryColor: '#1890ff',
    },
  },
];

/** @type {import('@docusaurus/types').Config} */
const config = {
  title: 'JNOJ',
  tagline: 'Online Judge for ICPC/IOI',
  url: 'https://www.jnoj.dev',
  baseUrl: '/',
  onBrokenLinks: 'throw',
  onBrokenMarkdownLinks: 'warn',
  favicon: 'img/favicon.ico',

  // GitHub pages deployment config.
  // If you aren't using GitHub pages, you don't need these.
  organizationName: 'shi-yang', // Usually your GitHub org/user name.
  projectName: 'jnoj', // Usually your repo name.

  // Even if you don't use internalization, you can use this field to set useful
  // metadata like html lang. For example, if your site is Chinese, you may want
  // to replace "en" with "zh-Hans".
  i18n: {
    defaultLocale: 'zh-Hans',
    locales: ['zh-Hans'],
  },

  presets: [
    [
      'classic',
      /** @type {import('@docusaurus/preset-classic').Options} */
      ({
        docs: {
          sidebarPath: require.resolve('./sidebars.js'),
          // Please change this to your repo.
          // Remove this to remove the "edit this page" links.
          editUrl:
            'https://github.com/shi-yang/jnoj/tree/main/packages/create-docusaurus/templates/shared/',
        },
        blog: {
          showReadingTime: true,
          // Please change this to your repo.
          // Remove this to remove the "edit this page" links.
          editUrl:
            'https://github.com/shi-yang/jnoj/tree/main/packages/create-docusaurus/templates/shared/',
        },
        theme: {
          customCss: require.resolve('./src/css/custom.css'),
        },
      }),
    ],
    // @ts-ignore
    redocusaurus,
  ],

  themeConfig:
    /** @type {import('@docusaurus/preset-classic').ThemeConfig} */
    ({
      navbar: {
        title: 'JNOJ',
        logo: {
          alt: 'My Site Logo',
          src: 'img/logo.svg',
        },
        items: [
          {
            type: 'doc',
            docId: 'intro',
            position: 'left',
            label: '使用教程',
          },
          {
            label: 'API',
            position: 'left',
            items: [
              {label: 'All', to: '/apis'},
              {label: 'User', to: '/api/interface/v1/user'},
              {label: 'Group', to: '/api/interface/v1/group'},
              {label: 'Contest', to: '/api/interface/v1/contest'},
              {label: 'Problem', to: '/api/interface/v1/problem'},
              {label: 'Submission', to: '/api/interface/v1/submission'},
              {label: 'Sanbox', to: '/api/interface/v1/sandboxs'},
            ]
          },
          {to: '/blog', label: '博客', position: 'left'},
          {to: 'https://sijicode.com', label: 'Demo', position: 'right'},
          {
            href: 'https://github.com/shi-yang/jnoj',
            label: 'GitHub',
            position: 'right',
          }
        ],
      },
      footer: {
        style: 'dark',
        links: [
          {
            title: 'Docs',
            items: [
              {
                label: 'Tutorial',
                to: '/docs/intro',
              },
            ],
          },
          {
            title: 'Community',
            items: [
              {
                label: 'Issues',
                href: 'https://github.com/shi-yang/jnoj',
              },
            ],
          },
          {
            title: 'More',
            items: [
              {
                label: 'Blog',
                to: '/blog',
              },
              {
                label: 'GitHub',
                href: 'https://github.com/shi-yang/jnoj',
              },
            ],
          },
        ],
        copyright: `Copyright © ${new Date().getFullYear()} shi-yang. Built with Docusaurus.`,
      },
    }),
};

module.exports = config;
