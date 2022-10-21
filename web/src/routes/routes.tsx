import React, { lazy } from 'react';
import { useRoutes } from 'react-router-dom';

import MainLayout from '@/components/Layouts/MainLayout';

const Home = lazy(() => import('@/pages/home/index'));
const About = lazy(() => import('@/pages/home/about'));

const ProblemIndex = lazy(() => import('@/pages/problem/index'))
const ProblemCreate = lazy(() => import('@/pages/problem/create'))
const ProblemUpdate = lazy(() => import('@/pages/problem/update'))
const ProblemDetail = lazy(() => import('@/pages/problem/detail'))

const UserLogin = lazy(() => import('@/pages/user/login/index'))
const UserRegister = lazy(() => import('@/pages/user/register'))
const UserView = lazy(() => import('@/pages/user/view'))

const ContestIndex = lazy(() => import('@/pages/contest/list'))
const ContestDetail = lazy(() => import('@/pages/contest/detail'))
const ContestUpdate = lazy(() => import('@/pages/contest/update'))

const routeList = [
  {
    path: "/",
    element: <MainLayout />,
    children: [
      { index: true, element: <Home /> },
      { path: '/about', element: <About /> },
      { path: '/problems', element: <ProblemIndex /> },
      { path: '/problems/:id', element: <ProblemDetail /> },
      { path: '/problem/create', element: <ProblemCreate /> },
      { path: '/problem/update/:id', element: <ProblemUpdate /> },

      { path: '/contests', element: <ContestIndex /> },
      { path: '/contests/:id/*', element: <ContestDetail /> },
      { path: '/contests/:id/setting', element: <ContestUpdate /> },

      { path: '/login', element: <UserLogin /> },
      { path: '/register', element: <UserRegister /> },
      { path: '/users/:id', element: <UserView /> },
    ],
  },
];

const RenderRouter = () => {
  return useRoutes(routeList);
};

export default RenderRouter;
