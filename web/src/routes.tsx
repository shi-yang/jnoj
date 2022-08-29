import React, { lazy } from "react";
import { useRoutes } from "react-router-dom";

import MainLayout from '@/components/Layouts/MainLayout';

const Home = lazy(() => import('@/pages/home/Index'));

const routeList = [
  {
    path: "/",
    element: <MainLayout />,
    children: [
      {
        index: true,
        element: <Home />,
      },
    ],
  },
];

const RenderRouter = () => {
  const element = useRoutes(routeList);
  console.log(element);
  return element;
};

export default RenderRouter;
